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