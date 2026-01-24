<?php

namespace JoelGrondrup\StripeSubscriptions\Pages;

use PageController;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use SilverStripe\Core\Environment;
use JoelGrondrup\StripeSubscriptions\Models\StripePlan;
use SilverStripe\Security\Member;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Core\Injector\Injector;

class DashboardPageController extends PageController 
{

    private static $allowed_actions = [
        
    ];

    public function index()
    {
        return $this->renderWith(['DashboardPage', 'Page']);
    }

}