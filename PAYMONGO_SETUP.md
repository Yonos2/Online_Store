# PayMongo test-mode setup

Online checkout uses PayMongo Hosted Checkout. Payment details are entered on
PayMongo's page; this application does not collect card details.

## Configure XAMPP

1. Create and verify a PayMongo account, then use its **test mode** secret key.
2. Set these environment variables for the Apache service (do not put the
   secret key in PHP source code or commit it):

   - `PAYMONGO_SECRET_KEY` — test secret key beginning with `sk_test_`
   - `PAYMONGO_WEBHOOK_SECRET` — test webhook signing secret
   - `APP_BASE_URL` — application root, for example
     `http://localhost/online_store`

3. Restart Apache so PHP receives the environment variables.
4. Apply `database/paymongo_checkout.sql` to the PostgreSQL database.
5. In PayMongo test mode, register a webhook pointing to
   `https://your-public-host/payments/webhook` and subscribe to
   `checkout_session.payment.paid`. Copy the webhook's test signing secret to
   `PAYMONGO_WEBHOOK_SECRET`.

The webhook must be reachable over HTTPS. For local testing, use a secure
tunnel and set `APP_BASE_URL` to its public HTTPS URL. The PayMongo option
stays disabled until a test secret key is configured. This integration
intentionally rejects live keys; live mode needs a separate review and test.

See PayMongo's official
[Hosted Checkout quick start](https://docs.paymongo.com/docs/payment-channels-hosted-checkout-quick-start.md)
and [test-mode guide](https://docs.paymongo.com/docs/payment-channels-testing.md).
