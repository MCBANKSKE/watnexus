# Plan: Ensure Companies Can Connect WhatsApp Accounts & Configure Meta Communication

## Goal
Fix critical bugs blocking WhatsApp account connection, document the Meta ↔ application communication model, and provide a step-by-step guide for Meta-side configuration.

## Current State Assessment

### What Already Exists
| Component | File(s) | Status |
|-----------|---------|--------|
| WhatsApp account model | `app/Models/WhatsAppAccount.php`, migration `2026_08_10_172316_create_whats_app_accounts_table.php` | Complete |
| Connection method columns | migration `2026_08_30_102414_add_connection_methods_to_whatsapp_accounts_table.php` | Complete (manual/oauth/qr_code) |
| Phone number model | `app/Models/WhatsAppPhoneNumber.php`, migration `2026_08_10_172845_create_whats_app_phone_numbers_table.php` | Complete |
| Manual connection service | `app/Services/WhatsApp/Authentication/ConnectWhatsAppService.php` | Implemented |
| Phone number sync | `app/Services/WhatsApp/Authentication/SyncWhatsAppPhoneNumbersService.php` | Implemented |
| Connection test | `app/Services/WhatsApp/Authentication/TestWhatsAppConnectionService.php` | Implemented |
| OAuth connect service | `app/Services/WhatsApp/Authentication/OAuthConnectService.php` | Implemented (with bugs) |
| OAuth callback service | `app/Services/WhatsApp/Authentication/OAuthCallbackService.php` | Implemented (with bugs) |
| QR code generation | `app/Services/WhatsApp/Authentication/GenerateQrCodeService.php` | Implemented (with bugs) |
| QR code verification | `app/Services/WhatsApp/Authentication/VerifyQrCodeConnectionService.php` | Implemented (with bugs) |
| OAuth web controller | `app/Http/Controllers/WhatsAppOAuthController.php` | Implemented (with bugs) |
| Web auth controller (dead) | `app/Http/Controllers/WhatsApp/WhatsAppAuthController.php` | **Empty stubs** |
| WhatsApp API trait | `app/Services/WhatsApp/Concerns/InteractsWithWhatsAppApi.php` | Complete |
| Webhook controller | `app/Http/Controllers/Webhook/WhatsAppWebhookController.php` | Implemented |
| Webhook signature verify | `app/Services/WhatsApp/Webhooks/VerifyWebhookSignatureService.php` | Complete |
| Webhook event processing | `app/Services/WhatsApp/Webhooks/ProcessWhatsAppWebhookService.php` | Complete |
| Webhook job | `app/Jobs/ProcessWebhookEventJob.php` | Implemented |
| Message sending (text/template/media) | `app/Services/WhatsApp/Messaging/*.php` | Implemented |
| Template sync/create/delete | `app/Services/WhatsApp/Templates/*.php` | Implemented |
| Filament resources | `app/Filament/Resources/WhatsAppAccounts/*` | Implemented (with gaps) |
| Routes | `routes/web.php`, `routes/api.php` | Configured (with conflicts) |

---

## Critical Bugs Found

### BUG 1 (CRITICAL BLOCKER): WhatsAppAccount model missing fillable fields
**File**: `app/Models/WhatsAppAccount.php:14-22`

The `$fillable` array does NOT include `connection_method`, `oauth_user_id`, `oauth_token`, or `qr_code_data`. Both `OAuthCallbackService` (line 79) and `VerifyQrCodeConnectionService` (line 48) call `WhatsAppAccount::create([...])` with `connection_method` — these fields are **silently dropped** by mass assignment protection. Result: all accounts are saved as `connection_method = 'manual'` (the DB default), and OAuth tokens aren't stored.

**Fix**: Add `connection_method`, `oauth_user_id`, `oauth_token`, and `qr_code_data` to `$fillable`.

### BUG 2 (CRITICAL SECURITY): oauth_token not hidden from JSON serialization
**File**: `app/Models/WhatsAppAccount.php:24-26`

`$hidden` only includes `access_token`. The `oauth_token` column is exposed if the model is serialized.

**Fix**: Add `oauth_token` to `$hidden`.

### BUG 3 (CRITICAL BLOCKER): WhatsAppAuthController has empty stub methods
**File**: `app/Http/Controllers/WhatsApp/WhatsAppAuthController.php:18-30`

The `redirect()` and `callback()` methods are **empty stubs**. The README (line 316) instructs users to visit `/whatsapp/auth/redirect`, but this produces no output. The actual OAuth implementation lives in `WhatsAppOAuthController` at different routes (`/whatsapp/oauth/authorize`).

**Fix**: Either wire `WhatsAppAuthController` to delegate to `WhatsAppOAuthController`, or remove the dead controller and update README/web.php routes to point to `/whatsapp/oauth/authorize`.

### BUG 4 (HIGH): OAuth state generation TypeError risk
**File**: `app/Services/WhatsApp/Authentication/OAuthConnectService.php:13`

`getAuthorizationUrl(string $state = null)` calls `$this->generateState()` (no argument) when `$state` is null, but `generateState(int $companyId)` requires a company ID. This causes a `TypeError` if called without arguments.

**Fix**: Make `generateState` accept `?int $companyId = null`, or make `getAuthorizationUrl` require the state parameter.

### BUG 5 (HIGH): QR code service uses non-existent Meta endpoint
**File**: `app/Services/WhatsApp/Authentication/VerifyQrCodeConnectionService.php:27,73` and `GenerateQrCodeService.php:29`

`VerifyQrCodeConnectionService` calls `/{appId}/whatsapp_embedded_signup` which is **not a valid Meta Graph API endpoint**. `GenerateQrCodeService` correctly uses `/{config_id}/accounts` for the embedded signup creation, but the verification/polling endpoint is wrong.

**Fix**: Research and use the correct Meta Embedded Signup polling endpoint. The real flow uses the config ID from the embedded signup configuration, not the app ID. Verify against Meta's current API docs.

### BUG 6 (HIGH): Wrong Meta API endpoint for WABA lookup in OAuth flow
**File**: `app/Services/WhatsApp/Authentication/OAuthCallbackService.php:57-58`

Uses `/me/subscribed_apps` to find the WABA. The correct endpoint to list WhatsApp Business Accounts for a user is `/me/business_accounts` or `GET /me?fields=whatsapp_business_account`. `/me/subscribed_apps` returns app subscription metadata, not WABA data, so `$wabaData['data'][0]['waba_id']` is unlikely to exist.

**Fix**: Use `/me/business_accounts` and extract the WABA ID from the response.

### BUG 7 (MEDIUM): CompanyScope defined but never applied as a global scope
**File**: `app/Models/Concerns/BelongsToCompany.php`, `app/Models/Scopes/CompanyScope.php`

`BelongsToCompany` trait only auto-fills `company_id` on create. The `CompanyScope` class exists but is **never registered** via `addGlobalScope()`. The comments reference CompanyScope, implying it should be applied. Without a global scope, Filament resources and direct queries could access other companies' records.

**Fix**: Register `CompanyScope` as a global scope in the `BelongsToCompany` boot method, or confirm it's intentionally not used and rely on middleware/context filtering.

### BUG 8 (MEDIUM): .env.example missing OAuth config
**File**: `.env.example:73`

The file is missing `WHATSAPP_OAUTH_CONFIG_ID` and `WHATSAPP_OAUTH_SUCCESS_REDIRECT`, which are referenced in `config/services.php:44-46`.

**Fix**: Add these to `.env.example`.

---

## Communication Model: Meta ↔ Application

### Outbound (Application → Meta)
All outbound calls use the WhatsApp Cloud API on `graph.facebook.com`.

| Purpose | Endpoint | Auth |
|---------|----------|------|
| Exchange OAuth code | `POST /oauth/access_token` | App credentials |
| Refresh OAuth token | `POST /oauth/access_token` | App credentials |
| List WABAs | `GET /me/business_accounts` | User token |
| Sync phone numbers | `GET /{waba_id}/phone_numbers` | Access token |
| Sync templates | `GET /{waba_id}/message_templates` | Access token |
| Submit template | `POST /{waba_id}/message_templates` | Access token |
| Delete template | `DELETE /{template_id}` | Access token |
| Send message | `POST /{phone_number_id}/messages` | Phone number token |
| Upload media | `POST /{phone_number_id}/media` | Phone number token |
| Get media | `GET /{media_id}` | Phone number token |
| Embedded signup (QR) | `POST /{config_id}/accounts` | App access token |

**Key pattern**: The `access_token` stored on the `WhatsAppAccount` model is used for account-level operations (WABA, phone numbers, templates). Per-phone-number token resolution happens in `InteractsWithWhatsAppApi::accessTokenFor()` which loads the account associated with a phone number.

### Inbound (Meta → Application)
Meta pushes events to our webhook endpoint:

```
POST /api/v1/webhooks/whatsapp
```

1. **HMAC verification**: `VerifyWebhookSignatureService` checks `X-Hub-Signature-256` against `WHATSAPP_APP_SECRET`.
2. **Event parsing**: `ProcessWhatsAppWebhookService` resolves the `WhatsAppAccount` and `WhatsAppPhoneNumber` from the webhook payload, stores each change as a `WebhookEvent` record, and dispatches `ProcessWebhookEventJob`.
3. **Async processing**: `ProcessWebhookEventJob` routes events:
   - **Status updates** (`messages` field) → `MessageStatusService::applyStatus()` updates message status with lifecycle progression (queued → sent → delivered → read → failed).
   - **Inbound messages** (`messages` field with direction inbound) → Creates `Contact`, `Conversation`, and `Message` records.

### Subscription Verification Handshake
```
GET /api/v1/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token={token}&hub_challenge={challenge}
```
`WhatsAppWebhookController::verify()` validates the token and returns the challenge as plain text.

### Rate Limiting & Error Handling
- Outgoing HTTP requests use a 15-second timeout (configurable via `InteractsWithWhatsAppApi::apiHttp()`).
- Outbound message sends are queued via `SendWhatsAppMessageJob` with 3 retries and exponential backoff `[15, 60, 300]`.
- Webhook processing is similarly queued with retry logic.

---

## Actionable Implementation Plan

### Phase 1: Fix Critical Blockers (Bug Fixes)

#### Task 1.1 — Fix WhatsAppAccount model fillable + hidden
**File**: `app/Models/WhatsAppAccount.php`

Add to `$fillable`: `connection_method`, `oauth_user_id`, `oauth_token`, `qr_code_data`
Add to `$hidden`: `oauth_token`

#### Task 1.2 — Fix OAuth state generation TypeError
**File**: `app/Services/WhatsApp/Authentication/OAuthConnectService.php`

Make `generateState` parameter nullable (`?int $companyId = null`) or make `getAuthorizationUrl` require state. Ensure the Filament form's OAuth button calls `getAuthorizationUrl` with a proper state.

#### Task 1.3 — Resolve dead WhatsAppAuthController
**Files**: `app/Http/Controllers/WhatsApp/WhatsAppAuthController.php`, `routes/web.php`, `README.md`

Option A: Wire `WhatsAppAuthController::redirect()` to return the OAuth authorization URL JSON (delegating to `OAuthConnectService`).
Option B: Remove `WhatsAppAuthController` routes from `web.php` and update README to use `WhatsAppOAuthController` routes (`/whatsapp/oauth/authorize`).

**Decision point**: See Question 1 below.

#### Task 1.4 — Fix OAuth WABA lookup endpoint
**File**: `app/Services/WhatsApp/Authentication/OAuthCallbackService.php`

Replace `/me/subscribed_apps` with `/me/business_accounts` and adjust field extraction. Test the response structure.

#### Task 1.5 — Add token refresh automation
**New file**: `app/Console/Commands/RefreshExpiredWhatsAppTokens.php`

Create an Artisan command that finds OAuth-connected accounts with expiring tokens and calls `OAuthCallbackService::refreshToken()`. Schedule it via `app/Console/Kernel.php` to run hourly.

#### Task 1.6 — Fix .env.example
**File**: `.env.example`

Add `WHATSAPP_OAUTH_CONFIG_ID` and `WHATSAPP_OAUTH_SUCCESS_REDIRECT`.

---

### Phase 2: API-level WhatsApp Connection

#### Task 2.1 — Add API endpoints for manual WhatsApp connection
**New file**: `app/Http/Controllers/Api/V1/WhatsAppAccountController.php`

Create API endpoints under the authenticated API group:
- `POST /api/v1/whatsapp/accounts/connect` — connect with WABA ID + access token (permission: `whatsapp.connect`)
- `GET /api/v1/whatsapp/accounts` — list connected accounts
- `GET /api/v1/whatsapp/accounts/{account}` — show account details (with phone numbers)
- `POST /api/v1/whatsapp/accounts/{account}/test` — test connection (permission: `whatsapp.connect`)
- `POST /api/v1/whatsapp/accounts/{account}/disconnect` — disconnect

Add `whatsapp.connect` permission to the permission enum in `ApiKeyService`.

**Rationale**: Companies connecting programmatically (not via browser) need an API path to connect their WhatsApp account.

#### Task 2.2 — Register API routes
**File**: `routes/api.php`

Add the new API controller routes under the authenticated group.

#### Task 2.3 — Add API controller tests
**New file**: `tests/Feature/WhatsAppConnectionApiTest.php`

Test that a company can connect/disconnect a WhatsApp account via the API, that permissions are enforced, and that multi-tenant isolation prevents accessing other companies' accounts.

---

### Phase 3: Meta Configuration Guide

#### Step 1: Meta Developer Account & App
1. Go to [Meta for Developers](https://developers.facebook.com) and sign in with a Meta account.
2. Click **My Apps** → **Create App** → select **Business** as the app type.
3. Name the app (e.g., "WatNexus WhatsApp") and select your Business Manager.
4. Click **Add Products** → find **WhatsApp** → click **Set Up**.

#### Step 2: Configure WhatsApp Product
1. In the app dashboard, go to **WhatsApp** → **Getting Started**.
2. Note the **WhatsApp Business Account ID** (this becomes your WABA ID).
3. Add your phone number via **WhatsApp** → **Send & Receive** → **Add Phone Number** (or use the test number).

#### Step 3: Generate System User & Token
1. Go to **Business Settings** → **System Users** → **Create System User** (e.g., "WatNexus Bot").
2. Assign the system user the **WhatsApp Business Account** admin role.
3. Click **Generate Token** → select scopes:
   - `whatsapp_business_management`
   - `whatsapp_business_messaging`
   - `whatsapp_business_messaging_phone_number`
4. Copy the token and store it as `WHATSAPP_ACCESS_TOKEN` in `.env` (or use OAuth instead).

#### Step 4: Configure Webhooks (Critical)
1. In Meta Business Suite → **WhatsApp** → **Configuration** → **Webhooks**.
2. Set **Callback URL** to: `https://{your-domain}/api/v1/webhooks/whatsapp`
3. Set **Verify Token** to match `WHATSAPP_WEBHOOK_VERIFY_TOKEN` in `.env`.
4. Click **Verify and Save**.
5. Subscribe to these fields:
   - `messages` (inbound messages)
   - `message_statuses` (delivery/read/failed receipts)

#### Step 5: OAuth Redirect URL (if using OAuth)
If using the OAuth flow (`connect_method = 'oauth'`), register the redirect URL in your Meta app:
1. Go to Meta Developer App Dashboard → **WhatsApp** → **Configuration** → **OAuth Settings**.
2. Add **Valid OAuth Redirect URI**: `https://{your-domain}/whatsapp/oauth/callback`
3. Note the **OAuth Config ID** and store it as `WHATSAPP_OAUTH_CONFIG_ID`.

#### Step 6: Environment Variables
```bash
WHATSAPP_API_URL=https://graph.facebook.com
WHATSAPP_GRAPH_VERSION=v23.0
WHATSAPP_APP_ID=your_app_id_from_meta
WHATSAPP_APP_SECRET=your_app_secret_from_meta
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_secure_random_token
WHATSAPP_OAUTH_CONFIG_ID=your_oauth_config_id  # only if using OAuth
WHATSAPP_OAUTH_SUCCESS_REDIRECT=/whatsapp/accounts
```

#### Step 7: Verify Connectivity
1. Ensure your application is accessible over **HTTPS** (Meta requires HTTPS for webhooks).
2. Run the webhook verification test:
   ```bash
   curl "https://your-domain.com/api/v1/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token={your_token}&hub_challenge=test123"
   ```
   Should return `test123` with HTTP 200.
3. Test message sending via the API (see API usage below).

---

### Phase 4: Validation & Testing

#### Validation Steps
1. **Webhook connectivity**: Confirm Meta can reach the webhook endpoint and the verify handshake succeeds.
2. **Account connection**: Connect a WhatsApp account via the chosen method (manual API, OAuth, or QR) and verify:
   - `whatsapp_accounts` table has the correct `connection_method`
   - `access_token` is stored encrypted
   - Phone numbers are synced to `whatsapp_phone_numbers`
3. **Message sending**: Send a test text message and confirm it arrives on WhatsApp. Then check the webhook delivers back a `sent` → `delivered` → `read` status progression.
4. **Inbound messages**: Send a message from WhatsApp to the connected number and verify it appears in the `conversations` and `messages` tables via the webhook.
5. **Token refresh**: For OAuth connections, verify the refresh command runs and extends the token expiry.

#### Test Command
```bash
php artisan test --filter=WhatsApp
```

---

### Phase 5: QR Code Flow (Lower Priority — Blocked on Meta API Research)

The `GenerateQrCodeService` and `VerifyQrCodeConnectionService` use endpoints that need verification against Meta's actual Embedded Signup API. This flow requires:
- A valid `whatsapp_business_verification` and embedded signup config ID
- Researching the correct Meta API endpoints for QR code generation and status polling

Mark as blocked pending Meta API research. The manual and OAuth flows should be the primary path.

---

## Risks & Edge Cases

| Risk | Mitigation |
|------|------------|
| Mass assignment silently drops OAuth fields | Fix `$fillable` on WhatsAppAccount model (Task 1.1) |
| OAuth token leaked in JSON responses | Add `oauth_token` to `$hidden` (Task 1.1) |
| Meta rejects webhook callback (HTTP 403) | Ensure `WHATSAPP_APP_SECRET` is set; signature verification must pass |
| Token expires and messages fail silently | Implement hourly token refresh (Task 1.5) |
| Company isolation broken in Filament | Investigate CompanyScope registration (Task 1.7) |
| Webhook not HTTPS in production | Meta requires HTTPS for webhooks; use reverse proxy (nginx/caddy) with SSL |
| Multi-tenant webhook routing: multiple companies share one webhook URL | The `ProcessWhatsAppWebhookService` resolves the account by `business_account_id` from the webhook payload — ensure each company's WABA ID is unique (it is, by Meta's design) |
| Phone number ID mismatch: webhook delivers events for a phone_number_id not synced locally | `ProcessWhatsAppWebhookService::resolvePhoneNumber` returns null silently — log a warning if phone number can't be resolved |

---

## Open Questions

1. **Should we keep both `WhatsAppAuthController` (web.php `/whatsapp/auth/*`) and `WhatsAppOAuthController` (web.php `/whatsapp/oauth/*`)?**

   The codebase has two controllers for overlapping functionality. `WhatsAppAuthController` has empty stubs. Recommended: **Remove `WhatsAppAuthController`** and its routes, update README to use `WhatsAppOAuthController` routes (`/whatsapp/oauth/authorize`), and add API-level connection endpoints. The Filament form already calls `OAuthConnectService::getAuthorizationUrl()` directly (line 82 of `WhatsAppAccountForm.php`).

2. **Should the QR code connection method be supported given the endpoint uncertainty?**

   The `GenerateQrCodeService` uses `POST /{config_id}/accounts` which is the correct Meta Embedded Signup endpoint, but `VerifyQrCodeConnectionService` uses `GET /{app_id}/whatsapp_embedded_signup` which is not a real endpoint. Recommended: **Mark QR code flow as experimental/blocked** until Meta's actual embedded signup polling API is verified. Focus on manual and OAuth flows.

3. **Should CompanyScope be registered as a global scope on tenant models?**

   The `CompanyScope` class exists but is never applied. Multi-tenant isolation currently works through: (a) the `BelongsToCompany` trait auto-filling `company_id`, (b) explicit relationship queries like `$company->contacts()`, and (c) the `CompanyContext` set by API middleware. Recommended: **Audit Filament resources** to ensure they don't expose cross-company data, then decide whether to register the global scope. This is a security-critical decision.
