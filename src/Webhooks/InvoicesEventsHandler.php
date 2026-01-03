<?php

namespace JoelGrondrup\StripeSubscriptions\Webhooks;

use Stripe\Event;
use JoelGrondrup\StripeSubscriptions\Models\Invoice;
use JoelGrondrup\StripeSubscriptions\Models\InvoiceLineItem;
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

        $invoiceid = $data->data->object->id ?? null;
        $customerid = $data->data->object->customer ?? null;
        $invoicedata = $data->data->object ?? null;

        switch ($event) {

            case 'invoice.created':
            case 'invoice.updated':

                $invoice = Invoice::get()->filter('StripeID', $invoiceid)->first();

                if (!$invoice) {
                    $invoice = Invoice::create();
                }

                // Link to Customer and Member records
                $member = Member::get()->filter('StripeCustomerID', $customerid)->first();

                $invoice->update([
                    'StripeID'        => $invoicedata->id,
                    'AmountDue'       => $invoicedata->amount_due,
                    'AmountPaid'      => $invoicedata->amount_paid,
                    'AmountRemaining' => $invoicedata->amount_remaining,
                    'Currency'        => $invoicedata->currency,
                    'Status'          => $invoicedata->status,
                    'InvoiceURL'      => $invoicedata->hosted_invoice_url,
                    'PDFURL'          => $invoicedata->invoice_pdf,
                    'PeriodStart'     => date('Y-m-d H:i:s', $invoicedata->period_start),
                    'PeriodEnd'       => date('Y-m-d H:i:s', $invoicedata->period_end),
                    'LiveMode'        => $invoicedata->livemode,
                    'MemberID'        => $member ? $member->ID : 0,
                ]);

                $invoice->write();

                if (isset($invoicedata->lines->data)) {
                    foreach ($invoicedata->lines->data as $line) {
                        $item = InvoiceLineItem::get()->filter('StripeID', $line->id)->first();
                        
                        if (!$item) {
                            $item = InvoiceLineItem::create();
                        }

                        $item->update([
                            'StripeID'    => $line->id,
                            'InvoiceID'   => $invoice->ID,
                            'Amount'      => $line->amount,
                            'Currency'    => $line->currency,
                            'Description' => $line->description,
                            'Quantity'    => $line->quantity,
                            'PriceID'     => $line->price->id ?? null,
                            'ProductID'   => $line->price->product ?? null,
                        ]);
                        
                        $item->write();
                    }
                }

                error_log("Invoice created");

                return "Invoice created";
            
            default:
                
                return "No event to handle";

        }

    }

}