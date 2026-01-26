<?php

namespace JoelGrondrup\StripeSubscriptions\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Group;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Environment;
use Stripe\Invoice;
use Stripe\Stripe;
use SilverStripe\View\ArrayData;

class StripeMemberExtension extends DataExtension
{

    /**
     * Define the default group code here.
     * This can be overridden by users in their own app/_config/config.yml
     */
    private static $active_group_code = 'active-subscribers';

    /**
     * Default mapping of Stripe statuses to SilverStripe Group Codes.
     * Use Config::inst()->get() to fetch these.
     */
    private static $status_group_mappings = [
        'active' => 'active-subscribers',
        'trialing' => 'active-subscribers',
        'past_due' => 'past-due-subscribers'
    ];
    
    private static $db = [
        'StripeCustomerID' => 'Varchar(255)',
        'SubscriptionStatus' => 'Varchar(50)', // active, trialing, past_due, canceled
        'SubscriptionID' => 'Varchar(255)',
        'Phone'             => 'Varchar(50)',
        'Description'       => 'Text',
        'Currency'          => 'Varchar(3)',
        'Balance'           => 'Int',          // Stripe balances are in cents
        'Delinquent'        => 'Boolean',
        'InvoicePrefix'     => 'Varchar(50)',
        'TaxExempt'         => 'Enum("none, exempt, reverse", "none")',
        'LiveMode'          => 'Boolean',
        'CreatedInStripe'   => 'Datetime',     // To store the 'created' timestamp
        'RawData'           => 'Text',         // Store the full JSON just in case
        'Metadata'          => 'Text'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Stripe', [
            ReadonlyField::create('StripeCustomerID', 'Stripe Customer ID'),
            ReadonlyField::create('SubscriptionStatus', 'Subscription Status'),
            ReadonlyField::create('SubscriptionID', 'Active Subscription ID'),
        ]);
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // Only sync groups if the status was changed during the save
        // or if this is a brand new record.
        if ($this->owner->isChanged('SubscriptionStatus') || $this->owner->isObjectCreated) {
            $this->updateSubscriptionGroups();
        }
    }

    protected function updateSubscriptionGroups()
    {
        // Force lowercase to match YAML keys exactly
        $status = strtolower($this->owner->SubscriptionStatus);
        
        $mappings = Config::inst()->get(self::class, 'status_group_mappings');

        if (!$mappings) {
            return;
        }

        // 1. Remove user from all possible subscription groups
        foreach ($mappings as $groupCode) {
            $group = Group::get()->filter('Code', $groupCode)->first();
            if ($group) {
                // This happens immediately in the many_many table
                $this->owner->Groups()->remove($group);
            }
        }

        // 2. Add to the specific group for the current status
        if (isset($mappings[$status])) {
            $targetCode = $mappings[$status];

            error_log("Trying to add to group: " . $targetCode);

            $targetGroup = Group::get()->filter('Code', $targetCode)->first();
            
            if ($targetGroup) {

                error_log("Adding member with ID " . $this->owner->ID . " to group: " . $targetGroup->Code . " with ID: " . $targetGroup->ID);

                $this->owner->Groups()->add($targetGroup);
            }
        }
        else {

            error_log("Stripe Extension Error: No group mapping found for status '$status'. Check your YAML config.");

        }
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        $mappings = $this->owner->config()->get('status_group_mappings');
        
        foreach ($mappings as $code) {
            if (!Group::get()->filter('Code', $code)->exists()) {
                $group = Group::create();
                $group->Title = 'Stripe ' . ucfirst(str_replace('-', ' ', $code));
                $group->Code = $code;
                $group->write();
            }
        }
    }

    public function getLastStripeInvoice()
    {
        if (!$this->owner->StripeCustomerID) {
            return null;
        }

        \Stripe\Stripe::setApiKey(\SilverStripe\Core\Environment::getEnv('STRIPE_SECRET'));

        try {
            $invoices = Invoice::all([
                'customer' => $this->owner->StripeCustomerID,
                'limit' => 1,
            ]);

            if (isset($invoices->data[0])) {
                // THE FIX: Wrap the Stripe object in ArrayData
                return ArrayData::create($invoices->data[0]->toArray());
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function getLastInvoiceAmount() 
    {
        $invoice = $this->getLastStripeInvoice();
        return $invoice ? ($invoice->total / 100) : 0;
    }

    public function getStripePortalLink() 
    {
        Stripe::setApiKey(Environment::getEnv('STRIPE_SECRET'));
        
        $session = \Stripe\BillingPortal\Session::create([
            'customer' => $this->owner->StripeCustomerID,
            'return_url' => $this->owner->AbsoluteBaseURL() . 'account/',
        ]);

        return $session->url;
    }

}