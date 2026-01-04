## SilverStripe Stripe Subscriptions

A lightweight SilverStripe module to manage Stripe subscriptions, synchronize Member groups based on payment status, and protect content with a subscription-based paywall.

## Features

- **Automated Member Sync** — Links Stripe Customers to SilverStripe `Member`s via email or ID.
- **Smart Group Mapping** — Moves members between Active, Restricted, and Expired groups based on Stripe subscription status.
- **Content Paywall** — Protect any Page or DataObject with a "Require Subscription" checkbox in the CMS.
- **Webhook Handlers** — Handlers for Customer, Subscription and Invoice events (built on top of a Stripe webhook module).

## Requirements

- `silverstripe/framework: ^4.0 || ^5.0`
- `vulcan/silverstripe-stripe-webhook: ^2.0` (or a compatible webhook handler)

## Installation

1. Require the module using Composer:

```bash
composer require joelgrondrup/silverstripe-stripe-subscriptions
```

2. Run a dev/build and flush the cache:

```text
http://your-site/dev/build?flush=1
```

## Configuration

1) Define your group mappings (example `app/_config/subscribers.yml`):

```yaml
JoelGrondrup\StripeSubscriptions\Extensions\StripeMemberExtension:
  status_group_mappings:
    active: 'active-subscribers'
    trialing: 'active-subscribers'
    past_due: 'restricted-access'
    canceled: 'expired-members'
    unpaid: 'expired-members'
```

2) Apply the paywall extensions (example `app/_config/app.yml`):

```yaml
SilverStripe\CMS\Model\SiteTree:
  extensions:
    - JoelGrondrup\StripeSubscriptions\Extensions\StripeProtectedExtension

SilverStripe\CMS\Controllers\ContentController:
  extensions:
    - JoelGrondrup\StripeSubscriptions\Extensions\StripeProtectedControllerExtension
```

After deploying this config, pages will show a **Require Subscription** checkbox under the Settings tab in the CMS.

## Usage

- Protecting individual pages: check the **Require Subscription** box on a page's Settings tab; only logged-in members in your configured "active" group can view the page.

- Global page type protection: set this property on a Page subclass to always require a subscription:

```php
private static $always_require_subscription = true;
```

- Template helpers: check a user's subscription status in `.ss` templates:

```ss
<% if $CurrentUser %>
  <% if $CurrentUser.IsSubscribed %>
    <p>Welcome back, Premium Member!</p>
  <% else %>
    <p>Your subscription status: $CurrentUser.SubscriptionStatus</p>
  <% end %>
<% end %>
```

## Testing with the Stripe CLI

Use the Stripe CLI to simulate events for a given customer email (example):

```bash
# Trigger a subscription.created event for an email
stripe trigger customer.subscription.created --add customer:email="test@example.com"
```

This will help you validate group synchronization and webhook handling locally.

## Notes

- This module expects a working Stripe webhook integration. If you use a different webhook module, ensure compatibility.
- Adjust group codes in the mappings to match your `Group` codes in SilverStripe.

---

If you want, I can also add a short example showing how to map a Stripe Customer ID to a Member or add troubleshooting tips.