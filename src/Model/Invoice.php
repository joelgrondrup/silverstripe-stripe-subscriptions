<?php

namespace JoelGrondrup\StripeSubscriptions\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Invoice extends DataObject
{
    private static $table_name = 'StripeInvoice';

    private static $db = [
        'StripeID'          => 'Varchar(255)',
        'AmountDue'         => 'Int',
        'AmountPaid'        => 'Int',
        'AmountRemaining'   => 'Int',
        'Subtotal'          => 'Int', // Added
        'Tax'               => 'Int', // Added
        'Currency'          => 'Varchar(3)',
        'Status'            => 'Varchar(50)',
        'BillingReason'     => 'Varchar(50)', // Added
        'AttemptCount'      => 'Int',         // Added
        'InvoiceURL'        => 'Text',
        'PDFURL'            => 'Text',
        'PeriodStart'       => 'Datetime',
        'PeriodEnd'         => 'Datetime',
        'LiveMode'          => 'Boolean',
        'Metadata'          => 'Text',
        'RawData'           => 'Text'         // Added
    ];

    private static $has_one = [
        'Member'   => Member::class, // Link directly to the user for easy lookup
    ];

    private static $has_many = [
        'LineItems' => InvoiceLineItem::class,
    ];

    private static $summary_fields = [
        'Created.Nice' => 'Date',
        'StripeID'     => 'Invoice ID',
        'AmountDue'    => 'Amount',
        'Status'       => 'Status'
    ];
}