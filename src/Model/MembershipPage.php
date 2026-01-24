<?php

namespace JoelGrondrup\StripeSubscriptions\Pages;

use Page;
use JoelGrondrup\StripeSubscriptions\Models\StripePlan;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Models\Link;

class MembershipPage extends Page 
{

    private static string $icon_class = 'font-icon-p-cart';

    private static $has_one = [
        "ThankYouPage" => Link::class,
    ];

    private static $has_many = [
        'Plans' => StripePlan::class
    ];

    private static array $owns = [
        'ThankYouPage',
        'Plans'
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

        $fields->addFieldToTab('Root.ThankYou', LinkField::create("ThankYouPage"));

        return $fields;
    }
}