# silverstripe-stripewebhook

Fork from vulcandigital/silverstripe-stripewebhook to update its use in a few ways:

-   Replace yml configuration with .env variables
-   Updated version of `stripe/stripe-php` and updated composer.json
-   Support multiple stripe accounts

This module is a Stripe webhook event handling delegation interface, a subclass can handle one or
more event and an event can be handled by one or more subclass

## Requirements

-   silverstripe/framework: ^4
-   stripe/stripe-php: ^7.43

## Configuration

replace `<key>`, and write within the `""`.

```
STRIPE_SECRET="<key>"
STRIPE_WEBHOOK_SECRET="<key>"
```

You can also use test keys and the webhook simulator will work fine with this module

## Usage

1. Install and dev/build
1. Add a webhook endpoint to Stripe that points to https://yourdomain.com/stripe-webhook and ensure that it sends the events you require
1. Create your functionality for your event(s):

```php
<?php
use Stripe\Event;
use Vulcan\StripeWebhook\Handlers\StripeEventHandler;
use SilverStripe\Security\Member;

class CustomerEventsHandler extends StripeEventHandler
{
    private static $events = [
        'customer.created',
        'customer.deleted'
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
```

Any subclass of `StripeEventHandler` is detected and requires both the `private static $events`
and `public static function handle($event, $data)` to be defined.

`private static $events` must be defined and can be a string containing a single [event identifier](https://stripe.com/docs/api#event_types) or an array with multiple

`public static function handle($event,$data)` must be defined and should not call the parent. \$data will be a `\Stripe\Event` object which has the exact same hierarchy as the JSON response depicted in their examples.

## Handling multiple Stripe accounts

If you would like to utilize more than one Stripe account, you need to make two changes. The first is so enter both or more sets of env vars in the following format:

-   `STRIPE_<NAME>_SECRET` eg. `STRIPE_STANDARD_SECRET` and `STRIPE_SECONDARY_SECRET`
-   `STRIPE_<NAME>_WEBHOOK_SECRET` eg. `STRIPE_STANDARD_WEBHOOK_SECRET` and `STRIPE_SECONDARY_WEBHOOK_SECRET`

You should also, for your own use, create `STRIPE_<NAME>_PUBLIC`, but this is not utilized by the webhook package.

Secondly - when generating a payment that requires the webhook, or, from Stripe itself, you need to include under the data -> object -> metadata, a variable named `stripe_account`that includes the naming, including case, such as `STANDARD` or `SECONDARY`. Otherwise the webhook will assume that the standard naming conventions of the envrionment variables will be present.

An example of when using Stripe checkout, when generating the Stripe Session, you would internally have used the correct stripe keys to generate the correct URLs, and when parsing the following information (as an example) you would use the correct naming in the metadata:

```php
public function MakeStripeSession($data, $standard = false)
{
    $stripe_secret_key = '';

    if ($standard) {
        $stripe_secret_key = Environment::getEnv('STRIPE_STANDARD_SECRET');
    } else {
        $stripe_secret_key = Environment::getEnv('STRIPE_SECONDARY_SECRET');
    }

    $stripe = new \Stripe\StripeClient($stripe_secret_key);

    $sessionInformation = [
        'payment_method_types' => ['card'],
        'line_items' => [
            [
            'price_data' => [
                'currency' => 'nzd',
                'product_data' => [
                'name' => $data->Name,
                ],
                // price in cents
                'unit_amount' => $data->TotalCost,
            ],
            'quantity' => 1,
            ],
        ],
        'mode' => 'payment',
        'payment_intent_data' => [
            'capture_method' => 'manual',
            'metadata' => [
                'stripe_account' => $standard ? 'STANDARD' : 'SECONDARY', // supporting multi .env / multi stripe account webhook return
            ]
        ],
        'success_url' => 'https://www.example.com/success',
        'cancel_url' => 'https://www.example.com/cancel',
    ];

    return $stripe->checkout->sessions->create($sessionInformation);
}
```

When this same payment is uncaptured, or whatever your requirements are - and the webhook returns this information to your website - the metadata is present and the correct env variable is used when handling the webhook return, whatever your desired result is. I have only been needing this functionality on stripe checkout, with standard non-product based payments via payment_intent_data. Other circumstances are not currently supported.

## Features

-   All _handled_ events are logged, along with the responses from their handlers.
-   Duplicates are ignored, if Stripe sends the same event more than once it won't be processed, but the logged event will count the occurence
-   All events are verified to have been sent from Stripe using your endpoint_secret you defined in the configuration above

## Why?

Easily introduce new event handling functionality without needing to touch any files relating to other event handling classes.

## License

[BSD-3-Clause](LICENSE.md) - [Vulcan Digital Ltd](https://vulcandigital.co.nz) (original authors - all rights remain theirs.)
