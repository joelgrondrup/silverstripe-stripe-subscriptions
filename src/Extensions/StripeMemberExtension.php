<?php

namespace JoelGrondrup\StripeSubscriptions\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ReadonlyField;

class StripeMemberExtension extends DataExtension
{
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
}