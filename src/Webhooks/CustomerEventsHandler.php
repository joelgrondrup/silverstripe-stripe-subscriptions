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
        'customer.subscription.updated'
    ];

    public static function handle($event, Event $data)
    {

        $obj = $data->data->object;

        $customerid = ($event === 'customer.deleted' || $event === 'customer.created' || $event === 'customer.updated') 
            ? $obj->id 
            : $obj->customer;

        if (!$customerid) {

            error_log("Missing customer ID");
            error_log(json_encode($data));

            return "Missing customer ID";
        }
        
        error_log("Event received: " . $event);
        error_log("Customer ID: " . $customerid);

        $member = Member::get()->filter('StripeCustomerID', $customerid)->first();

        switch ($event) {

            case 'customer.created':
            case 'customer.updated':

                if (!$member) {
                    $member = Member::create();
                }

                $member->update([
                    'StripeCustomerID'  => $obj->id,
                    'Email'             => $obj->email,
                    'Name'              => $obj->name,
                    'Phone'             => $obj->phone,
                    'Description'       => $obj->description,
                    'Currency'          => $obj->currency,
                    'Balance'           => $obj->balance,
                    'Delinquent'        => $obj->delinquent,
                    'InvoicePrefix'     => $data->invoice_prefix,
                    'TaxExempt'         => $obj->tax_exempt,
                    'LiveMode'          => $obj->livemode,
                    'CreatedInStripe'   => date('Y-m-d H:i:s', $obj->created),
                    'Metadata'          => json_encode($obj->metadata),
                    'RawData'           => json_encode($data),
                    'SubscriptionStatus' => 'active'
                ]);

                $member->write();

                return sprintf(
                    "Member (%s) created. Security groups synchronized.",
                    $member->Email
                );

            case 'customer.subscription.created':
            case 'customer.subscription.updated':

                if (!$member) {
                    
                    return sprintf(
                        "Member (%s) not found. Security groups synchronized.",
                        $customerid
                    );

                }

                $oldStatus = $member->SubscriptionStatus;
                $newStatus = $obj->status;

                $member->SubscriptionStatus = $obj->status; 
                $member->write(); // Triggers Group Sync

                return sprintf(
                    "Member (%s) updated: %s -> %s. Security groups synchronized.",
                    $member->Email,
                    $oldStatus,
                    $newStatus
                );

            case 'customer.subscription.deleted':

                if (!$member) {
                    
                    return sprintf(
                        "Member (%s) not found. Security groups synchronized.",
                        $customerid
                    );
                    
                }

                $member->SubscriptionStatus = 'canceled';
                $member->write(); // Triggers Group Sync (Moves to Expired Group)

                return sprintf(
                    "Member (%s) subscription cancelled: %s. Security groups synchronized.",
                    $member->Email,
                    $member->SubscriptionStatus
                );

            case 'customer.deleted':

                if (!$member) {

                    return sprintf(
                        "Member (%s) not found. Security groups synchronized.",
                        $customerid
                    );

                }

                $member->delete();

                return sprintf(
                    "Member (%s) deleted. Security groups synchronized.",
                    $member->Email
                );

            default:
                
                return "No handler for event: " . $event;

        }


    }

}