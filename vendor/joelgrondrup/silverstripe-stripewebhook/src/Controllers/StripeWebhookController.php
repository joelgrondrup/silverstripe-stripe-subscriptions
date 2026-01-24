<?php

namespace Vulcan\StripeWebhook\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use Stripe\Event;
use Vulcan\StripeWebhook\Handlers\StripeEventHandler;
use Vulcan\StripeWebhook\Models\EventOccurrence;
use Vulcan\StripeWebhook\StripeWebhook;

class StripeWebhookController extends Controller
{
    private static $allowed_actions = [
        'index'
    ];

    public function init()
    {
        parent::init();
    }

    /**
     * @param HTTPRequest $request
     *
     * @return \SilverStripe\Control\HTTPResponse
     */
    public function index(HTTPRequest $request)
    {
        $body = $request->getBody();
        $header = $request->getHeader('Stripe-Signature');
        $eventJson = json_decode($body, true);

        if (!$header) {
            $this->httpError(401);
        }

        if (!$eventJson) {
            $this->httpError(422, 'The body did not contain valid json');
        }

        $result = null;

        // if you have set a naming convention in the purchase metadata that comes back from the webhook response, it allows for a multi stripe account setup
        // the naming conventiion is STRIPE_{NAME}_SECRET and STRIPE_{NAME}_WEBHOOK_SECRET
        $secret_override = isset($eventJson['data']['object']['metadata']['stripe_account']) ? $eventJson['data']['object']['metadata']['stripe_account'] : '';

        $client = StripeWebhook::create($secret_override);

        try {
            $event = \Stripe\Webhook::constructEvent($body, $header, $client->getEndpointSecret($secret_override));

            if ($occurrence = EventOccurrence::getByEventID($event->id)) {
                // this event occurrence has already been processed
                // for some unknown reason lets record the fact that this event was
                // sent a number of times
                $occurrence->Occurrences = $occurrence->Occurrences + 1;
                $occurrence->write();

                return $this->getResponse()->setBody('OK - Duplicate');
            }

            $result = $this->delegateEvent($client, $event);

            if (!$result) {
                return $this->getResponse()->setBody('No handlers defined for event ' . $event->type);
            }

            $occurrence = EventOccurrence::create();
            $occurrence->EventID = $event->id;
            $occurrence->Type = $event->type;
            $occurrence->Data = $body;
            $occurrence->Handlers = implode(PHP_EOL, $result['Handlers']);
            $occurrence->HandlerResponses = implode(PHP_EOL, $result['Responses']);
            $occurrence->write();
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            $this->httpError(400);
        } catch (\Stripe\Error\SignatureVerification $e) {
            $this->httpError(400);
        }

        $break = (Director::is_cli()) ? PHP_EOL : "<br/>";
        return $this->getResponse()->setBody(implode($break, $result['Responses']));
    }

    /**
     * @param Event $event
     *
     * @return array|null
     */
    public function delegateEvent($client, Event $event)
    {
        $handlers = $client->getHandlers();

        if (!isset($handlers[$event->type])) {
            return null;
        }


        $responses = [];
        /**
         *
         * @var StripeEventHandler $class
        */
        foreach ($handlers[$event->type] as $class) {
            $response = $class::handle($event->type, $event);
            $responses[] = $class . ':' . $response ?: "NULL";
        }

        return [
            'Handlers' => $handlers[$event->type],
            'Responses' => $responses
        ];
    }
}
