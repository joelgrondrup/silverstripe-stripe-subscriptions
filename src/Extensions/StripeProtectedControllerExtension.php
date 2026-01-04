<?php

namespace JoelGrondrup\StripeSubscriptions\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Control\Controller;

class StripeProtectedControllerExtension extends Extension
{
    public function onBeforeInit()
    {
        $owner = $this->owner;
        $dataRecord = $owner->data();

        // Check if the page has our extension and if the user is allowed
        if ($dataRecord->hasMethod('canViewSubscription') && !$dataRecord->canViewSubscription()) {
            // Redirect to a pricing page or login page
            // You could make this configurable in YAML
            return Controller::curr()->redirect('/membership/pricing/');
        }
    }
}