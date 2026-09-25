# Connecting a Company to WhatsApp Cloud API

This application uses the **Meta system-user token** connection path. It is the supported operational path for a multi-company platform: each company supplies the WABA ID and a token generated for a system user that has access to that WABA. Do not use a personal Facebook user token in production.

## 1. Prepare Meta

1. Create a Meta app, add the **WhatsApp** product, and complete the required Business Verification steps for the company/WABA.
2. In Meta Business Settings, create a system user for the company (or grant a platform-owned system user access to that company's WABA). Assign the app and WhatsApp permissions required for messaging and WABA management.
3. Generate a production system-user access token for that user. Store it only in the application's WhatsApp Account form; it is encrypted at rest by the `WhatsAppAccount` model.
4. Copy the **WhatsApp Business Account ID (WABA ID)** from WhatsApp Manager. The phone-number ID is different; the application retrieves it after connection.

## 2. Configure the application

Set the following environment values and restart/re-cache configuration:

```dotenv
APP_URL=https://app.example.com
WHATSAPP_API_URL=https://graph.facebook.com
WHATSAPP_GRAPH_VERSION=v23.0
WHATSAPP_APP_ID=your_meta_app_id
WHATSAPP_APP_SECRET=your_meta_app_secret
WHATSAPP_WEBHOOK_VERIFY_TOKEN=a-long-random-secret
```

`WHATSAPP_APP_SECRET` is used only to validate Meta's webhook signature; never send it to a browser. `WHATSAPP_WEBHOOK_VERIFY_TOKEN` is an application-chosen shared value used once during the webhook verification handshake.

## 3. Connect a company

1. Sign in as a user with an active membership for the target company.
2. Open **Admin → WhatsApp Accounts → Create**.
3. Enter an account label, WABA ID, and the system-user access token.
4. Save. WatNexus calls `GET /{WABA_ID}` with that token. If Meta accepts it, it calls `GET /{WABA_ID}/phone_numbers`, stores the returned phone-number IDs, and marks the account connected. A rejected request remains `pending` and must not be used to send messages.
5. Use the account table's **Test** and **Sync numbers** actions after changing a token or phone-number configuration.

The create flow derives `company_id` from the authenticated active membership rather than form input. This is required to avoid connecting an account to another company.

## 4. Enable Meta → WatNexus webhooks

In the Meta app's WhatsApp configuration, set the callback URL to:

```text
https://app.example.com/api/v1/webhooks/whatsapp
```

Enter the exact `WHATSAPP_WEBHOOK_VERIFY_TOKEN` value and subscribe the app to the WhatsApp webhook fields needed by the product, at least **messages**. Meta sends a `GET` with `hub.mode`, `hub.verify_token`, and `hub.challenge`; WatNexus returns the challenge only when the token matches. Meta then sends signed `POST` deliveries. WatNexus validates `X-Hub-Signature-256` as HMAC-SHA256 of the raw body using `WHATSAPP_APP_SECRET`, persists the event, and queues processing.

The callback must be publicly reachable over HTTPS. Do not put an authentication page, VPN-only gateway, or body-rewriting proxy in front of it. Ensure the queue worker is continuously running so accepted deliveries are processed:

```bash
php artisan queue:work
```

## 5. Prove communication end to end

Use this release checklist in a staging Meta app before enabling a company in production:

1. Meta's callback verification succeeds.
2. Create the account and confirm the UI reports a successful Meta connection and one or more synced phone numbers.
3. Click **Test**; it must report a valid token.
4. Send a permitted test message from a synced phone number and confirm the outgoing Graph API response is stored.
5. Reply to the WhatsApp number. Confirm a `webhook_events` row is created, then confirm the queue changes it to `processed` and creates/updates the expected conversation and message records.
6. Check Meta's webhook delivery logs and the application's failed-jobs/logging for errors. Rotate a compromised or expiring system-user token, update it in the account, then repeat steps 3–5.

## Operating rules

- Treat WABA ID, phone-number ID, system-user token, Meta App Secret, and webhook verify token as different values with different uses.
- Subscribe each WABA/app pairing in Meta before relying on inbound messages or delivery statuses.
- Keep the Graph version configurable; update it deliberately after staging verification, rather than hard-coding a version in requests.
- Monitor token validity, webhook delivery failures, queued/failed jobs, and phone-number quality rating. A `connected` database status is not a substitute for a successful live **Test**.
