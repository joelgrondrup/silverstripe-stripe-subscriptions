<?php

namespace JoelGrondrup\StripeSubscriptions\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;

class StripeProtectedExtension extends DataExtension
{
    private static $db = [
        'RequireSubscription' => 'Boolean'
    ];

    public function updateSettingsFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            'Root.Settings',
            CheckboxField::create('RequireSubscription', 'Require an active Stripe subscription to view this page')
        );
    }

    /**
     * This method can be called in your controller's init()
     */
    public function canViewSubscription()
    {
        // If the page doesn't require a subscription, anyone can view
        $forced = $this->owner->config()->get('always_require_subscription');
    
        if ($forced || $this->owner->RequireSubscription) {
            $member = Security::getCurrentUser();
            return ($member && $member->inGroup('active-subscribers'));
        }

        return false;
    }

}