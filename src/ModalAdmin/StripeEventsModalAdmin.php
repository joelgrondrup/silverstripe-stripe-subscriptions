<?php

use SilverStripe\Admin\ModelAdmin;
use Colymba\BulkManager\BulkManager;
use Vulcan\StripeWebhook\Models\EventOccurrence;

class StripeEventsModelAdmin extends ModelAdmin 
{

    private static $managed_models = [
        Vulcan\StripeWebhook\Models\EventOccurrence::class
    ];

    private static $url_segment = 'stripeevents';

    private static $menu_title = 'Stripe events';

    
}