<?php

namespace JoelGrondrup\StripeSubscriptions\Pages;

use PageController;
use SilverStripe\Security\Security;

class DashboardPageController extends PageController 
{

    private static $allowed_actions = [
        
    ];

    public function init(){

        parent::init();

    }

    public function index()
    {

        $this->extend('onDashboardLoad');

        return $this->renderWith(['DashboardPage', 'Page']);
    }

    public function canViewSubscription()
    {
        $member = Security::getCurrentUser();
        
        // Example logic: Only allow if user has an active 'Gold' subscription
        if ($member && $member->SubscriptionStatus === 'active') {
            return true;
        }
        
        return false;
    }

}