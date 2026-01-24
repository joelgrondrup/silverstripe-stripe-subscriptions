<?php

namespace JoelGrondrup\StripeSubscriptions\Pages;

use PageController;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use SilverStripe\Core\Environment;
use JoelGrondrup\StripeSubscriptions\Models\StripePlan;
use SilverStripe\Security\Member;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Core\Injector\Injector;

class MembershipPageController extends PageController 
{

    private static $allowed_actions = [
        'checkout', 
        'thankyou'
    ];

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
        error_log(Environment::getEnv('STRIPE_SECRET'));
        // Set your API Key (Usually from environment variables)
        Stripe::setApiKey(Environment::getEnv('STRIPE_SECRET'));

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
            'success_url' => $this->AbsoluteLink('thankyou') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->AbsoluteLink(),
        ]);

        error_log("Session ID: " . $session->id);

        $this->getRequest()->getSession()->set('StripeCheckoutSessionID', $session->id);
        
        return $this->redirect($session->url);
    }

    public function thankyou()
    {
        
        $sessionId = $this->getRequest()->getSession()->get('StripeCheckoutSessionID');

        if (!$sessionId) {
            return $this->redirect($this->Link()); // Redirect home if no ID is present
        }

        try {

            Stripe::setApiKey(Environment::getEnv('STRIPE_SECRET'));
            
            // Retrieve the full session object from Stripe using the ID
            $session = Session::retrieve($sessionId);

            error_log("Session ID:" . $session->id);
            
            // Check the payment status
            if ($session->payment_status === 'paid') {

                error_log(json_encode($session));

                $email = $session->customer_details->email;

                // 2. Find the Member by email
                $member = Member::get()->filter('Email', $email)->first();

                if ($member) {
                    // 3. Log the user in
                    $identityStore = Injector::inst()->get(IdentityStore::class);
                    $identityStore->logIn($member, true, $this->getRequest()); // true = remember me

                    // Clear the checkout session from our PHP session
                    $this->getRequest()->getSession()->clear('StripeCheckoutSessionID');

                    return [
                        'Title' => 'Welcome back, ' . $member->FirstName,
                        'Content' => '<p>You are now logged in and your subscription is active.</p>'
                    ];
                }
                else {

                    return [
                        'Title' => 'Success!',
                        'Content' => '<p>Thank you for your payment! Your account is being activated.</p>',
                        'StripeSession' => $session // Pass it to the template if needed
                    ];

                }

            }
            
        } catch (\Exception $e) {
            error_log("Stripe Error: " . $e->getMessage());
        }

        return [
            'Title' => 'Welcome to the Club!',
            'Content' => '<p>Your payment is being processed. Please wait a moment while we activate your account...</p>'
        ];
    }

}