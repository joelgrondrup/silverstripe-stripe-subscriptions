<?php

namespace JoelGrondrup\StripeSubscriptions\Extensions;

use SilverStripe\ORM\DataExtension;

class EventOccurrenceExtension extends DataExtension
{
    /**
     * This method overrides or adds to the summary fields 
     * displayed in GridFields/ModelAdmins.
     */
    public function updateSummaryFields(&$fields)
    {
        // Define exactly what you want to see in the list
        $fields = [
            'Created'     => 'Date',
            'Type'        => 'Event Type',
            'EventID'     => 'Stripe ID',
            'Occurrences' => 'Count'
        ];
    }
}