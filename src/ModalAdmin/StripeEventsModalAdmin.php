<?php

use JoelGrondrup\StripeSubscriptions\Models\Invoice;
use SilverStripe\Admin\ModelAdmin;
use Vulcan\StripeWebhook\Models\EventOccurrence;
use SilverStripe\Security\Member;

class StripeEventsModelAdmin extends ModelAdmin 
{

    private static $managed_models = [
        EventOccurrence::class => [
            'title' => 'Stripe Events'
        ],
        Member::class => [
            'title' => 'Stripe Customers'
        ],
        Invoice::class => [
            'title' => 'Stripe Invoice'
        ] 
    ];

    private static $url_segment = 'stripeevents';

    private static $menu_title = 'Subscriptions';

    public function getList()
    {
        $list = parent::getList();

        // If the current tab is for Members, apply the filter
        if ($this->modelClass === Member::class) {
            $list = $list->filter([
                'StripeCustomerID:not' => [null, '']
            ]);
        }
        else if ($this->modelClass === EventOccurrence::class) {
            $list = $list->sort("Created DESC");
        }

        return $list;
    }

    
}