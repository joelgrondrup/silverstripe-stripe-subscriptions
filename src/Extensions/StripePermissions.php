<?php

namespace JoelGrondrup\StripeSubscriptions\Extensions;

use SilverStripe\Security\PermissionProvider;

class StripePermissions implements PermissionProvider
{
    public function providePermissions()
    {
        return [
            'STRIPE_SUBSCRIBER_ACTIVE' => [
                'name' => 'Access to active subscription content',
                'category' => 'Stripe Subscriptions',
            ],
            'STRIPE_SUBSCRIBER_PAST_DUE' => [
                'name' => 'Access to "Past Due" account features',
                'category' => 'Stripe Subscriptions',
            ],
        ];
    }
}