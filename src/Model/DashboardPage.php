<?php

namespace JoelGrondrup\StripeSubscriptions\Pages;

use Page;
use JoelGrondrup\StripeSubscriptions\Models\StripePlan;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Models\Link;

class DashboardPage extends Page 
{

    private static string $icon_class = 'font-icon-dashboard';

    private static $has_one = [
        
    ];

    private static $has_many = [
        
    ];

    private static array $owns = [
        
    ];

    public function getCMSFields() 
    {
        $fields = parent::getCMSFields();

        

        return $fields;
    }
}