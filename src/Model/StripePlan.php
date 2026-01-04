<?php

namespace JoelGrondrup\StripeSubscriptions\Models;

use SilverStripe\ORM\DataObject;

class StripePlan extends DataObject 
{

    private static $table_name = 'StripePlan';

    private static $db = [
        'Title' => 'Varchar',
        'StripePriceID' => 'Varchar', // e.g., price_123...
        'Price' => 'Currency',
        'Description' => 'Text',
        'Features' => 'Text', // We can split this by new lines in the template
        'SortOrder' => 'Int'
    ];

    private static $has_one = [
        'MembershipPage' => 'Page'
    ];

    private static $summary_fields = [
        'Title' => 'Plan Name',
        'Price' => 'Price',
        'StripePriceID' => 'Stripe ID'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('MembershipPageID');
        $fields->removeByName('SortOrder');

        return $fields;
    }
}