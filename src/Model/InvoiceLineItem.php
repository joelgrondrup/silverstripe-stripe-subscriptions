<?php

namespace JoelGrondrup\StripeSubscriptions\Models;

use SilverStripe\ORM\DataObject;

class InvoiceLineItem extends DataObject
{
    private static $table_name = 'StripeInvoiceLineItem';

    private static $db = [
        'StripeID'    => 'Varchar(255)', // il_...
        'Amount'      => 'Int',          // 2000
        'Currency'    => 'Varchar(3)',   // usd
        'Description' => 'Text',         // "(created by Stripe CLI)"
        'Quantity'    => 'Int',          // 1
        'PriceID'     => 'Varchar(255)', // price_1SlYE...
        'ProductID'   => 'Varchar(255)', // prod_Tj0Fu...
    ];

    private static $has_one = [
        'Invoice' => Invoice::class,
    ];

    private static $summary_fields = [
        'Description' => 'Description',
        'Amount'      => 'Amount',
        'Quantity'    => 'Qty'
    ];
}