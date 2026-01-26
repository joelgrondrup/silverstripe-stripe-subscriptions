<?php

namespace JoelGrondrup\StripeSubscriptions\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Control\Controller;

class StripeProtectedControllerExtension extends Extension
{
    public function onBeforeInit()
    {
        $owner = $this->owner;

        // Check if the page has our extension and if the user is allowed
        if ($owner->hasMethod('canViewSubscription') && !$owner->canViewSubscription()) {
            // Redirect to a pricing page or login page
            // You could make this configurable in YAML
            return Controller::curr()->redirect('/membership/pricing/');
        }
    }
}