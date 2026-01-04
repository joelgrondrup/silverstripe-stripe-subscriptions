<?php

namespace JoelGrondrup\StripeSubscriptions\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Group;
use SilverStripe\Security\Permission;
use SilverStripe\Core\Config\Config;
use JoelGrondrup\StripeSubscriptions\Extensions\StripeMemberExtension;

class StripeGroupSyncTask extends BuildTask
{
    protected $title = 'Stripe Subscription Group Sync';
    protected $description = 'Ensures all Security Groups and Permissions required by the Stripe module exist.';

    public function run($request)
    {
        // Get the mappings from the Extension's config
        $mappings = Config::inst()->get(StripeMemberExtension::class, 'status_group_mappings');

        foreach ($mappings as $status => $code) {
            $group = Group::get()->filter('Code', $code)->first();

            if (!$group) {
                $group = Group::create();
                $group->Code = $code;
                $group->Title = 'Stripe ' . ucfirst($status) . ' Subscribers';
                $group->write();
                echo "Created group: $code <br>";
            }

            // Assign the specific permission to the active/trialing groups
            if (in_array($status, ['active', 'trialing'])) {
                Permission::grant($group->ID, 'STRIPE_SUBSCRIBER_ACTIVE');
                echo "Granted 'Active' permission to: $code <br>";
            }
        }
        
        echo "<strong>Sync Complete!</strong>";
    }
}