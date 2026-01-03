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

        $customerid = $data->data->object->id ?? null;
        error_log("Customer ID: " . $customerid);

        if (!$customerid){
            error_log("Missing customer ID");
            return "Missing customer ID";
        }

        switch ($event) {

            case 'customer.created':
            case 'customer.updated':

                $member = Member::get()->filter('StripeCustomerID', $customerid)->first();
                
                if (!$member) {
                    $member = Member::create();
                }

                $member->update([
                    'StripeCustomerID'=> $data->data->object->id,
                    'Email'           => $data->data->object->email,
                    'Name'            => $data->data->object->name,
                    'Phone'           => $data->data->object->phone,
                    'Description'     => $data->data->object->description,
                    'Currency'        => $data->data->object->currency,
                    'Balance'         => $data->data->object->balance,
                    'Delinquent'      => $data->data->object->delinquent,
                    'InvoicePrefix'   => $data->invoice_prefix,
                    'TaxExempt'       => $data->data->object->tax_exempt,
                    'LiveMode'        => $data->data->object->livemode,
                    'CreatedInStripe' => date('Y-m-d H:i:s', $data->data->object->created),
                    'Metadata'        => json_encode($data->data->object->metadata),
                    'RawData'         => json_encode($data)
                ]);

                $member->write();

                return "Customer created/updated";

            case 'customer.deleted':

                $member = Member::get()->filter('StripeCustomerID', $customerid)->first();

                if ($member) {

                    $member->delete();
                }

                return "Customer deleted";

            default:
                
                return "No event to handle";

        }


    }

}