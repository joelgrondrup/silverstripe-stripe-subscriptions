<?php

namespace JoelGrondrup\StripeSubscriptions\Webhooks;

use Stripe\Event;
use Vulcan\StripeWebhook\Handlers\StripeEventHandler;
use SilverStripe\Security\Member;

class CustomerEventsHandler extends StripeEventHandler
{

    private static $events = [
        'customer.created',
        'customer.deleted',
        'customer.subscription.created',
        'customer.subscription.deleted',
        'customer.subscription.trial_will_end',
        'customer.subscription.updated',
        ''
    ];

    public static function handle($event, Event $data)
    {
        // $event is the string identifier of the event
        if ($event == 'customer.created') {
            // create member
            return "Member created";
        }

        $member = Member::get()->filter('Email', $event->data->object->email)->first();

        if (!$member) {
            return "Member did not exist";
        }

        $member->delete();
        return "Member deleted";
    }

}