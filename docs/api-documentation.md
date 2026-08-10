# WatNexus WhatsApp API Documentation

**Base URL:** `https://{your-domain}/api/v1`
**Authentication:** `Authorization: Bearer {prefix}.{secret}` or `X-API-Key: {prefix}.{secret}`

> Webhook endpoints are NOT authenticated via API key — secured via HMAC signature verification.

## Endpoints Overview

| Method | Path | Auth | Permission |
|--------|------|------|------------|
| GET | `/status` | None | — |
| POST | `/webhooks/whatsapp` | HMAC | — |
| GET | `/webhooks/whatsapp` | HMAC | — |
| POST | `/messages/send` | API Key | `messages.send` |
| POST | `/messages/send-media` | API Key | `messages.send` |
| GET | `/messages/{message}` | API Key | `messages.read` |
| POST | `/otp/generate` | API Key | `otp.generate` |
| POST | `/otp/verify` | API Key | `otp.verify` |
| GET | `/templates` | API Key | `templates.read` |
| POST | `/templates` | API Key | `templates.create` |
| GET | `/templates/{template}` | API Key | `templates.read` |
| GET | `/contacts` | API Key | `contacts.read` |
| POST | `/contacts` | API Key | `contacts.create` |
| GET | `/campaigns` | API Key | `campaigns.read` |
| POST | `/campaigns` | API Key | `campaigns.create` |
| GET | `/campaigns/{campaign}` | API Key | `campaigns.read` |
| PUT | `/campaigns/{campaign}` | API Key | `campaigns.create` |
| DELETE | `/campaigns/{campaign}` | API Key | `campaigns.create` |
| POST | `/campaigns/{campaign}/send` | API Key | `campaigns.send` |
| GET | `/campaigns/{campaign}/recipients` | API Key | `campaigns.read` |

## Webhook Endpoints

### POST `/webhooks/whatsapp`
Receive webhook events from Meta (status updates and inbound messages).

**Security:** `X-Hub-Signature-256` HMAC verification against `WHATSAPP_APP_SECRET`.

**Response (200):**
```json
{ "success": true, "received": 1 }
```

### GET `/webhooks/whatsapp`
Meta's webhook subscription verification handshake.

**Query Params:** `hub_mode=subscribe`, `hub_verify_token={token}`, `hub_challenge={challenge}`

**Response (200):** Plain text `{challenge}`

## Message Endpoints

### POST `/messages/send`
Queue a text message for delivery.

**Request:**
```json
{ "to": "+254700000000", "message": "Hello!", "name": "John", "wa_id": "254700000000" }
```
**Response (202):**
```json
{ "success": true, "message": "Message queued for delivery.", "data": { "id": 1, "status": "queued" } }
```

### POST `/messages/send-media`
Upload media to Meta and queue a media message.

**Request:**
```json
{
  "to": "+254700000000",
  "type": "image",
  "media_url": "https://example.com/image.jpg",
  "caption": "Here is your image"
}
```
Supported types: `image`, `video`, `audio`, `document`, `sticker`
**Response (202):**
```json
{ "success": true, "message": "Media message queued for delivery.", "data": { "id": 1, "status": "queued", "media_id": "123456789" } }
```

### GET `/messages/{message}`
Retrieve a message with its status history.

## OTP Endpoints

### POST `/otp/generate` — 20 requests/min
**Request:** `{ "phone": "+254700000000", "channel": "sms", "expires_in_minutes": 10 }`

### POST `/otp/verify` — 20 requests/min
**Request:** `{ "phone": "+254700000000", "code": "123456" }`

## Template Endpoints

### GET `/templates`
List the company's message templates.

### POST `/templates`
Create a new template as a draft.

**Request:**
```json
{
  "name": "welcome_message",
  "language": "en",
  "category": "utility",
  "body": "Welcome to {{1}}!",
  "header": { "type": "text", "text": "Welcome" },
  "footer": "Powered by WatNexus",
  "buttons": [{ "type": "reply", "text": "Get Started", "value": "get_started" }],
  "variables": [{ "key": "1", "value": "MyCompany" }]
}
```

### GET `/templates/{template}`
Get a single template.

## Contact Endpoints

### GET `/contacts`
List contacts (paginated, 50/page).

### POST `/contacts`
Create or update a contact (upsert on phone).

## Campaign Endpoints

### GET `/campaigns`
List campaigns (paginated, 25/page).

### POST `/campaigns`
Create a new draft campaign.

**Request:**
```json
{
  "name": "Monthly Newsletter",
  "description": "Monthly update",
  "message_template_id": 1,
  "contact_ids": [1, 2],
  "contact_list_ids": [1],
  "scheduled_at": "2026-08-15T10:00:00Z"
}
```

### GET `/campaigns/{campaign}`
Get a campaign with statistics (counts + associated messages).

### PUT `/campaigns/{campaign}`
Update a draft/scheduled campaign.

### DELETE `/campaigns/{campaign}`
Delete a draft/scheduled campaign.

### POST `/campaigns/{campaign}/send`
Execute a campaign — dispatches messages to all recipients via queue.

### GET `/campaigns/{campaign}/recipients`
List campaign recipients with per-recipient delivery status (paginated, 50/page).

## General Endpoints

### GET `/status`
Health check — no authentication required.

**Response (200):**
```json
{ "success": true, "message": "API is healthy.", "data": { "service": "watnexus-api", "version": "v1" } }
```

## Error Responses

| Code | Message | Description |
|------|---------|-------------|
| 401 | `Missing API key.` / `Invalid or inactive API key.` | Authentication required |
| 403 | `This API key is not allowed from your IP address.` / `This API key does not have the required permission.` | Insufficient permissions |
| 404 | `Resource not found.` / `Campaign not found.` | Record not found |
| 409 | `No connected WhatsApp phone number...` / `Cannot delete a campaign that is currently running.` / `Cannot modify a campaign that has already started...` | State conflict |
| 422 | `The given data was invalid.` | Validation failed |
| 429 | `Too many requests. Please slow down.` | Rate limited |

## Rate Limits

| Tier | Limit | Scope |
|------|-------|-------|
| API | 120 requests/min | Per company |
| OTP | 20 requests/min | Per company |

## Available Permissions

`'-'`, `messages.send`, `messages.read`, `otp.generate`, `otp.verify`, `templates.create`, `templates.read`, `contacts.create`, `contacts.read`, `campaigns.create`, `campaigns.read`, `campaigns.send`