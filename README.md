# WatNexus - Multi-Company WhatsApp Messaging Platform

**A production-ready API platform for businesses to send WhatsApp messages, OTP codes, and marketing campaigns through a unified API.**

[![PHP Version](https://img.shields.io/badge/php-8.2+-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/laravel-12.0-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

---

## 🎯 What is WatNexus?

WatNexus is a **multi-tenant WhatsApp Business API platform** that enables businesses to:
- Connect their WhatsApp Business accounts
- Send text messages, media, templates, and OTP codes via API
- Manage contacts and run marketing campaigns
- Track message delivery and usage in real-time
- Handle webhooks for status updates and inbound messages
- Scale with proper queue processing and retry logic

**Built for businesses** that want to integrate WhatsApp messaging into their applications without dealing with Meta's complex API directly.

---

## ✨ Key Features

### 🚀 Core Messaging
- **Text Messages** - Send plain text messages via API
- **Media Messages** - Send images, videos, audio, documents, stickers
- **Template Messages** - Use approved WhatsApp templates
- **OTP/Verification** - Generate and verify one-time passwords
- **Campaigns** - Bulk messaging to contact lists with tracking

### 🔐 Security & Multi-Tenancy
- **Company Isolation** - Complete data separation between companies
- **API Key Authentication** - Secure key-based authentication with permissions
- **IP Restrictions** - Optional IP whitelisting per API key
- **Encrypted Credentials** - WhatsApp access tokens encrypted at rest
- **Permission System** - Granular access control (messages.send, otp.generate, etc.)

### 📊 Tracking & Analytics
- **Message Status Tracking** - Real-time delivery/read/failed status
- **Campaign Analytics** - Per-recipient delivery tracking
- **Usage Records** - Track usage for billing and limits
- **API Request Logging** - Complete audit trail of API usage
- **Webhook Processing** - Async processing of Meta webhooks

### 🏗️ Architecture
- **Queue-Based Processing** - All heavy operations run in background
- **Retry Logic** - Automatic retries with exponential backoff
- **Webhook Security** - HMAC signature verification
- **Rate Limiting** - Configurable rate limits per endpoint
- **Status Progression** - Proper message lifecycle (queued → sent → delivered → read)

---

## 📊 Current Status: ~90% Complete

### ✅ What's Been Done

#### **Core Infrastructure (100%)**
- ✅ Complete multi-tenant data model with 28+ tables
- ✅ Company, User, and role-based access control
- ✅ WhatsApp Account and Phone Number management
- ✅ Contact and Contact List management
- ✅ Conversation and Message tracking
- ✅ Campaign and Campaign recipient tracking
- ✅ OTP Verification system
- ✅ Usage tracking and billing preparation
- ✅ Webhook event storage and processing

#### **API Layer (100%)**
- ✅ Versioned API (v1) with proper structure
- ✅ 10+ API endpoints fully implemented
- ✅ Secure API key authentication system
- ✅ Permission-based access control middleware
- ✅ Request validation for all endpoints
- ✅ Standardized JSON response format
- ✅ Comprehensive error handling
- ✅ API request logging middleware
- ✅ Rate limiting configuration

#### **WhatsApp Integration (100%)**
- ✅ ConnectWhatsAppService for account connection
- ✅ SyncWhatsAppPhoneNumbersService for phone number sync
- ✅ TestWhatsAppConnectionService for connection testing
- ✅ SendTextMessageService for text messages
- ✅ SendTemplateMessageService for template messages
- ✅ SendMediaMessageService for media messages
- ✅ UploadMediaService for media upload to Meta
- ✅ Template sync and management services
- ✅ Message status tracking service

#### **Background Processing (100%)**
- ✅ SendWhatsAppMessageJob with retry logic
- ✅ ProcessWebhookEventJob for webhook processing
- ✅ RecordUsageJob for usage tracking
- ✅ SyncTemplatesJob for template synchronization
- ✅ Proper queue configuration and error handling

#### **Webhook System (100%)**
- ✅ WhatsAppWebhookController with verify/receive methods
- ✅ VerifyWebhookSignatureService for HMAC verification
- ✅ Webhook routes configured in api.php
- ✅ CSRF exemption for webhook endpoints
- ✅ Async webhook event processing

#### **Campaign Management (100%)**
- ✅ CampaignController with full CRUD operations
- ✅ Campaign execution via SendCampaignService
- ✅ Recipient tracking with delivery status
- ✅ Campaign statistics integration
- ✅ Contact and contact list attachment

#### **Documentation (95%)**
- ✅ Comprehensive API documentation (docs/api-documentation.md)
- ✅ All endpoints documented with examples
- ✅ Error response codes and messages
- ✅ Rate limiting and permission reference
- ✅ Webhook setup instructions

#### **Testing (20%)**
- ⚠️ Basic test infrastructure in place
- ⚠️ Only example tests exist
- ❌ No API endpoint tests
- ❌ No service layer tests
- ❌ No integration tests

---

## ❌ What's Remaining

### **Critical Issues (Must Fix Before Production)**

#### **1. Test Failures (High Priority)**
**Status**: 5 tests failing, need fixing

**Issues**:
- `ApiKeyPermissionTest::test_missing_campaigns_send_permission_returns_403` - Returns 404 instead of 403
- `TemplateFieldTest::template_show_returns_full_data` - Returns 403 instead of 200  
- `WebhookSignatureTest` - 3 tests failing due to signedRequest() method returning null

**Impact**: Core functionality not validated by tests

**Fix Required**:
```bash
# Fix campaign route model binding issue
# Fix template permission issue
# Fix webhook test helper method
```

**Time Estimate**: 1-2 hours

#### **2. Template Components Mismatch (Medium Priority)**
**Status**: TemplateController stores `components` as JSON, migration expects separate fields

**Issue**: Template model migration has separate fields (`header`, `buttons`, `variables`) but controller stores single `components` field

**Impact**: Templates may not work correctly for Meta API submission

**Fix Required**:
- Option A: Add `components` field to migration
- Option B: Parse components in controller and store in separate fields
- Option C: Create service to transform components format

**Time Estimate**: 1-2 hours

### **Missing Features (Nice to Have)**

#### **3. Contact List Management API (Low Priority)**
**Status**: No API endpoints for contact list CRUD

**Missing**:
- Create contact lists
- Add/remove contacts from lists
- List contact lists
- Contact list statistics

**Impact**: Contact lists can only be managed via database/admin UI

**Time Estimate**: 2-3 hours

#### **4. Template Meta Submission (Medium Priority)**
**Status**: No endpoint to submit templates to Meta for approval

**Missing**:
- Template submission endpoint
- Template sync from Meta
- Template deletion from Meta

**Impact**: Templates can only be created as local drafts, not used for WhatsApp

**Time Estimate**: 2-3 hours

#### **5. Campaign Scheduling (Low Priority)**
**Status**: Manual execution only

**Missing**:
- Scheduled campaign execution via queue/cron
- Campaign pause/resume functionality
- Scheduled campaign management

**Impact**: Campaigns must be triggered manually

**Time Estimate**: 3-4 hours

#### **6. Comprehensive Test Coverage (Medium Priority)**
**Status**: Only basic example tests exist

**Missing**:
- API endpoint tests (authentication, permissions, validation)
- Service layer tests (ApiKeyService, CampaignService, etc.)
- Job processing tests
- Webhook signature verification tests
- Integration tests for complete flows

**Impact**: High regression risk during changes

**Time Estimate**: 2-3 days

#### **7. Monitoring & Observability (Low Priority)**
**Status**: Basic logging only

**Missing**:
- Structured logging with context
- Performance monitoring
- Failed job alerting
- Webhook processing metrics
- API usage analytics dashboard

**Impact**: Difficult to troubleshoot production issues

**Time Estimate**: 1-2 days

---

## 🚀 Quick Start Guide

### **Prerequisites**
- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or PostgreSQL 12+
- Redis (recommended for production)
- Meta Developer Account with WhatsApp Business App

### **Installation**

```bash
# Clone the repository
git clone <repository-url>
cd watnexus

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env file
# Edit database credentials, WhatsApp credentials, etc.

# Run migrations
php artisan migrate

# Cache configuration
php artisan config:cache
php artisan route:cache

# Start queue worker (in separate terminal)
php artisan queue:work

# Start development server
php artisan serve
```

### **Initial Setup**

#### **1. Create Company and User**
```bash
php artisan tinker
```

```php
$company = \App\Models\Company::create([
    'name' => 'My Company',
    'slug' => 'my-company',
    'status' => 'active',
    'timezone' => 'Africa/Nairobi',
]);

$user = \App\Models\User::factory()->create([
    'name' => 'Admin User',
    'email' => 'admin@mycompany.com',
    'password' => bcrypt('password123'),
]);

$user->attachToCompany($company, 'admin');

echo "Company ID: {$company->id}\n";
echo "User ID: {$user->id}\n";
```

#### **2. Generate API Key**
```php
$company = \App\Models\Company::first();
$user = \App\Models\User::first();

$service = app(\App\Services\ApiKey\ApiKeyService::class);
$result = $service->generate(
    $company,
    $user,
    'Production API Key',
    ['*'], // Full permissions
    null, // No expiration
    [], // No IP restrictions
);

echo "Your API Key: {$result['plain_text_key']}\n";
// Save this key - you won't see it again!
```

#### **3. Connect WhatsApp Business Account**

**Option A: Use Web UI**
1. Visit: `http://localhost:8000/whatsapp/auth/redirect`
2. Complete Meta's Embedded Signup flow
3. Callback will connect your WhatsApp Business Account

**Option B: Manual Connection**
```php
$company = \App\Models\Company::first();
$user = \App\Models\User::first();

$service = app(\App\Services\WhatsApp\Authentication\ConnectWhatsAppService::class);
$account = $service->handle(
    $company,
    'your_waba_id', // From Meta Business Suite
    'your_access_token', // From Meta Business Suite
    'My WhatsApp Account'
);

// Sync phone numbers
$syncService = app(\App\Services\WhatsApp\Authentication\SyncWhatsAppPhoneNumbersService::class);
$syncService->handle($account);
```

#### **4. Configure Meta Webhook**

**Add to .env**:
```bash
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_secure_random_token_here
```

**Configure in Meta Business Suite**:
1. Go to WhatsApp → Configuration
2. Set webhook URL: `https://your-domain.com/api/v1/webhooks/whatsapp`
3. Set verify token (same as in .env)
4. Subscribe to: `messages`, `message_statuses`

---

## 🧪 Testing

### **Run All Tests**
```bash
php artisan test
```

### **Run Specific Test Suite**
```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit

# Specific test
php artisan test --filter=test_name
```

### **Current Test Status**
- **Total Tests**: 19
- **Passing**: 14
- **Failing**: 5
- **Coverage**: ~20%

---

## 📡 API Usage Examples

### **Health Check**
```bash
curl http://localhost:8000/api/v1/status
```

### **Send Text Message**
```bash
curl -X POST http://localhost:8000/api/v1/messages/send \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+254700000000",
    "message": "Hello from Nexus!"
  }'
```

### **Generate OTP**
```bash
curl -X POST http://localhost:8000/api/v1/otp/generate \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+254700000000",
    "length": 6,
    "expires_in_minutes": 5
  }'
```

### **Create Campaign**
```bash
curl -X POST http://localhost:8000/api/v1/campaigns \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Monthly Newsletter",
    "message_template_id": 1,
    "contact_ids": [1, 2, 3]
  }'
```

For complete API documentation, see [docs/api-documentation.md](docs/api-documentation.md)

---

## 🏭 Production Deployment

### **Server Requirements**
- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or PostgreSQL 12+
- **Cache/Queue**: Redis (recommended)
- **Web Server**: Nginx or Apache
- **Process Manager**: Supervisor (for queue workers)
- **SSL**: Let's Encrypt or commercial certificate

### **Environment Configuration**

Update `.env` for production:
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-production-host
DB_DATABASE=watnexus_production
DB_USERNAME=your-production-user
DB_PASSWORD=your-production-password

CACHE_STORE=redis
QUEUE_CONNECTION=redis

WHATSAPP_APP_ID=your_production_app_id
WHATSAPP_APP_SECRET=your_production_app_secret
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_production_verify_token
```

### **Queue Worker Setup**

**Install Supervisor** (Ubuntu/Debian):
```bash
sudo apt-get install supervisor
```

**Create supervisor config** (`/etc/supervisor/conf.d/watnexus-worker.conf`):
```ini
[program:watnexus-worker]
process_name=%(program_name)s_%(process_num)%
command=php /var/www/watnexus/artisan queue:work --sleep=3 --tries=3 --timeout=30
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/watnexus/worker.log
stopwaitsecs=3600
```

**Start supervisor**:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start watnexus-worker:*
```

### **SSL Setup (Required for Meta Webhooks)**
Meta requires HTTPS for webhooks. Use:
- Let's Encrypt (free)
- Cloudflare SSL
- Your hosting provider's SSL

---

## 🔧 Configuration

### **WhatsApp Configuration**
Add to `.env`:
```bash
WHATSAPP_API_URL=https://graph.facebook.com
WHATSAPP_GRAPH_VERSION=v23.0
WHATSAPP_APP_ID=your_app_id
WHATSAPP_APP_SECRET=your_app_secret
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_verify_token
```

### **Rate Limiting**
Configure in `config/throttle.php` (create if not exists):
```php
return [
    'api' => [
        'limit' => 120,
        'per_minute' => 60,
    ],
    'otp' => [
        'limit' => 20,
        'per_minute' => 60,
    ],
];
```

### **Middleware Aliases**
Already configured in `bootstrap/app.php`:
- `auth.apikey` - API key authentication
- `log.api` - API request logging
- `api.key.permission` - Permission checking

---

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── V1/           # API v1 controllers
│   │   │   └── ApiController.php
│   │   ├── Webhook/          # Webhook controllers
│   │   └── WhatsApp/          # WhatsApp auth controllers
│   ├── Middleware/            # Custom middleware
│   └── Requests/Api/V1/       # API request validation
├── Models/                   # Eloquent models
├── Services/
│   ├── ApiKey/               # API key management
│   ├── Campaign/              # Campaign services
│   ├── Messaging/             # Message status tracking
│   ├── Otp/                   # OTP services
│   ├── Usage/                 # Usage tracking
│   └── WhatsApp/
│       ├── Authentication/    # WhatsApp connection services
│       ├── Concerns/         # Shared WhatsApp API traits
│       ├── Media/            # Media upload/download
│       ├── Messaging/        # Message sending services
│       ├── Templates/        # Template management
│       └── Webhooks/          # Webhook processing
├── Jobs/                     # Queue jobs
└── Support/                  # Helper classes (ApiResponse, etc.)

database/
├── factories/                # Model factories
└── migrations/               # Database migrations

routes/
├── api.php                   # API routes
├── web.php                   # Web routes
└── console.php               # Console routes

tests/
├── Feature/                  # Feature tests
└── Unit/                     # Unit tests

docs/
└── api-documentation.md      # API documentation
```

---

## 🛠️ Development Workflow

### **Running the Application**

**Development Server**:
```bash
php artisan serve
```

**Queue Worker** (required for background processing):
```bash
php artisan queue:work
```

**Both at once** (using composer script):
```bash
composer run dev
```

### **Code Quality**

**Run code style fixes**:
```bash
php artisan pint
```

**Run static analysis**:
```bash
./vendor/bin/phpstan analyse
```

### **Database Migrations**

**Run migrations**:
```bash
php artisan migrate
```

**Rollback last migration**:
```bash
php artisan migrate:rollback
```

**Fresh migration**:
```bash
php artisan migrate:fresh
```

---

## 🐛 Troubleshooting

### **Queue Worker Not Processing Jobs**
```bash
# Check queue status
php artisan queue:failed

# Restart queue worker
php artisan queue:restart

# Clear queue cache
php artisan queue:clear
```

### **WhatsApp Connection Issues**
```bash
# Test connection in tinker
php artisan tinker
```

```php
$account = \App\Models\WhatsAppAccount::first();
$service = app(\App\Services\WhatsApp\Authentication\TestWhatsAppConnectionService::class);
$service->handle($account); // Returns true/false
```

### **Webhook Not Receiving Events**
1. Verify webhook URL is accessible: `curl https://your-domain.com/api/v1/webhooks/whatsapp`
2. Check `WHATSAPP_WEBHOOK_VERIFY_TOKEN` matches in Meta
3. Verify Meta subscription includes `messages` and `message_statuses`
4. Check logs: `tail -f storage/logs/laravel.log`

### **API Key Authentication Failing**
```bash
# Verify key format
# Should be: prefix.secret (e.g., wax_abc123xyz789)
```

### **Database Connection Issues**
```bash
# Clear config cache
php artisan config:clear

# Check database credentials in .env
php artisan tinker
```

```php
\DB::connection()->getPdo(); // Test connection
```

---

## 📚 Additional Resources

- **API Documentation**: [docs/api-documentation.md](docs/api-documentation.md)
- **Laravel Documentation**: [https://laravel.com/docs](https://laravel.com/docs)
- **WhatsApp Cloud API**: [https://developers.facebook.com/docs/whatsapp](https://developers.facebook.com/docs/whatsapp)
- **Laravel Queues**: [https://laravel.com/docs/queues](https://laravel.com/docs/queues)

---

## 🤝 Contributing

This is a production application. For major changes:
1. Create a feature branch
2. Write tests for new functionality
3. Ensure all tests pass
4. Submit a pull request

---

## 📄 License

This project is open-sourced software licensed under the MIT license.

---

## 🎯 Roadmap

### **Phase 1: Production Readiness (Current - 90%)**
- ✅ Core API infrastructure
- ✅ WhatsApp integration
- ✅ Background processing
- ✅ Webhook handling
- ✅ Campaign management
- ⚠️ Fix test failures
- ⚠️ Template components fix

### **Phase 2: Enhanced Features**
- Contact list management API
- Template Meta submission
- Campaign scheduling
- Comprehensive test coverage
- Monitoring and observability

### **Phase 3: Advanced Features**
- Multi-language support
- Advanced analytics dashboard
- Admin UI (Filament)
- Webhook retry strategies
- Rate limiting tiers

---

## 📞 Support

For issues, questions, or contributions, please open an issue in the repository.

---

**Built with ❤️ using Laravel 12.0 and WhatsApp Cloud API v23.0**
