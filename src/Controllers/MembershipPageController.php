<?php

namespace JoelGrondrup\StripeSubscriptions\Pages;

use PageController;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use SilverStripe\Core\Environment;
use JoelGrondrup\StripeSubscriptions\Models\StripePlan;

class MembershipPageController extends PageController 
{
    private static $allowed_actions = ['checkout', 'thankyou'];

    public function index()
    {
        return $this->renderWith(['MembershipPage', 'Page']);
    }

    public function checkout(): HTTPResponse
    {
        $planID = $this->request->getVar('plan');
        $plan = StripePlan::get()->byID($planID);

        if (!$plan) {
            return $this->httpError(404, 'Plan not found');
        }

        error_log('Starting checkout for plan: ' . $plan->Title);
        error_log(Environment::getEnv('STRIPE_SECRET_KEY'));
        // Set your API Key (Usually from environment variables)
        Stripe::setApiKey(Environment::getEnv('STRIPE_SECRET_KEY'));

        $member = Security::getCurrentUser();

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $plan->StripePriceID,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'customer' => $member ? $member->StripeCustomerID : null,
            'customer_email' => (!$member) ? $this->request->getVar('email') : null,
            'success_url' => $this->AbsoluteLink('thankyou?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => $this->AbsoluteLink(),
        ]);

        error_log($session->url);

        return $this->redirect($session->url);
    }

    public function thankyou()
    {
        $sessionId = $this->request->getVar('session_id');
        if (!$sessionId) {
            return $this->redirect($this->Link());
        }

        // Optional: Retrieve the session to see the customer ID
        // $session = \Stripe\Checkout\Session::retrieve($sessionId);

        return [
            'Title' => 'Welcome to the Club!',
            'Content' => '<p>Your payment is being processed. Please wait a moment while we activate your account...</p>'
        ];
    }

}