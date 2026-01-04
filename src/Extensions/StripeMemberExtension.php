<?php

namespace JoelGrondrup\StripeSubscriptions\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Group;
use SilverStripe\Forms\ReadonlyField;

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

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // Only run if the SubscriptionStatus has changed
        if ($this->owner->isChanged('SubscriptionStatus')) {
            $this->updateSubscriptionGroups();
        }
    }

    protected function updateSubscriptionGroups()
    {
        $status = $this->owner->SubscriptionStatus;
        
        // Fetch the mappings from the config system
        $mappings = $this->owner->config()->get('status_group_mappings');

        // 1. Remove user from all possible subscription groups first (to handle downgrades)
        foreach ($mappings as $groupCode) {
            $group = Group::get()->filter('Code', $groupCode)->first();
            if ($group) {
                $this->owner->Groups()->remove($group);
            }
        }

        // 2. Add to the specific group for the current status
        if (isset($mappings[$status])) {
            $targetCode = $mappings[$status];
            $targetGroup = Group::get()->filter('Code', $targetCode)->first();
            
            if ($targetGroup) {
                $this->owner->Groups()->add($targetGroup);
            }
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

}