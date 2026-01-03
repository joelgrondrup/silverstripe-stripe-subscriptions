<?php

namespace JoelGrondrup\StripeSubscriptions\Webhooks;

use Stripe\Event;
use Vulcan\StripeWebhook\Handlers\StripeEventHandler;
use SilverStripe\Security\Member;

class InvoicesEventsHandler extends StripeEventHandler
{

    private static $events = [
        'invoice.created',
        'invoice.deleted',
        'invoice.paid',
        'invoice.payment_action_required', // Occurs whenever an invoice payment attempt requires further user action to complete.
        'invoice.payment_failed',
        'invoice.payment_succeeded',
        'invoice.sent',
        'invoice.upcoming', //Occurs X number of days before a subscription is scheduled to create an invoice that is automatically charged—where X is determined by your subscriptions settings. Note: The received Invoice object will not have an invoice ID.
        
    ];

    public static function handle($event, Event $data)
    {
        // $event is the string identifier of the event
        if ($event == 'invoice.created') {
            // create member
            return "Invoice created";
        }

        return "Error";

    }

}