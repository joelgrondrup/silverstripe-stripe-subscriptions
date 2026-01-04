<?php

namespace JoelGrondrup\StripeSubscriptions\Pages;

use Page;
use JoelGrondrup\StripeSubscriptions\Models\StripePlan;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

class MembershipPage extends Page 
{
    private static $has_many = [
        'Plans' => StripePlan::class
    ];

    public function getCMSFields() 
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Plans', GridField::create(
            'Plans',
            'Subscription Plans',
            $this->Plans(),
            GridFieldConfig_RecordEditor::create()
        ));
        return $fields;
    }
}