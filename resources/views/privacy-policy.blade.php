@extends('layouts.app')

@section('title', 'Privacy Policy — WatNexus')
@section('description', 'Learn how WatNexus collects, uses, and protects your personal and business data on our WhatsApp Business messaging platform.')

@section('content')
    <section class="legal-page" style="max-width: 800px; margin: 0 auto; padding: 64px 24px;">
        <h1 style="font-family: 'Fraunces', serif; font-size: 36px; margin-bottom: 8px;">Privacy Policy</h1>
        <p style="font-family: 'IBM Plex Mono', monospace; font-size: 13px; color: #6b7280; margin-bottom: 32px;">Last updated: {{ now()->format('F j, Y') }}</p>

        <div style="line-height: 1.8; color: #374151;">
            <h2 style="font-size: 22px; margin: 32px 0 12px;">1. Introduction</h2>
            <p>WatNexus ("we", "our", "us") is a multi-tenant WhatsApp Business API platform that enables businesses to send WhatsApp messages, OTP verification codes, and marketing campaigns through a unified API. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">2. Information We Collect</h2>
            <ul>
                <li><strong>Account information</strong> — name, email address, and password when you register.</li>
                <li><strong>Company information</strong> — company name and business details provided during setup.</li>
                <li><strong>WhatsApp Business data</strong> — WhatsApp Business account IDs, phone numbers, and access tokens (encrypted at rest).</li>
                <li><strong>Messaging data</strong> — contacts, contact lists, message content, templates, campaigns, and delivery status records you create within the platform.</li>
                <li><strong>API usage data</strong> — API request logs, IP addresses, and device/browser information for security and auditing.</li>
            </ul>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">3. How We Use Your Information</h2>
            <ul>
                <li>To provide, operate, and maintain the WatNexus platform.</li>
                <li>To send messages, OTP codes, and campaigns on your behalf through the WhatsApp Business API.</li>
                <li>To track message delivery status and provide usage analytics.</li>
                <li>To authenticate API keys and enforce permissions and rate limits.</li>
                <li>To process Meta webhooks for status updates and inbound messages.</li>
                <li>To communicate with you about your account and service updates.</li>
                <li>To comply with legal and regulatory obligations.</li>
            </ul>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">4. Third-Party Services</h2>
            <p>Our platform integrates with <strong>Meta's WhatsApp Business API</strong>. Message content, recipient phone numbers, and related data are processed by Meta in accordance with Meta's own policies. We also use Google for sign-in (Socialite) where applicable.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">5. Data Security</h2>
            <p>We apply industry-standard security measures, including encrypted WhatsApp credentials at rest, HMAC-verified webhooks, API key authentication with optional IP whitelisting, and complete company-level data isolation between tenants. However, no method of transmission over the internet is 100% secure.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">6. Data Sharing</h2>
            <p>We do not sell your personal information. We share data only with Meta (as required to deliver WhatsApp messages), with service providers that help us operate the platform, or when required by law.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">7. Data Retention</h2>
            <p>We retain your information for as long as your account is active or as needed to provide services, comply with legal obligations, resolve disputes, and enforce our agreements.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">8. Your Rights</h2>
            <p>Depending on your jurisdiction, you may have the right to access, correct, export, or delete your personal data. See our <a href="{{ route('data-deletion') }}">Data Deletion instructions</a> or contact us at the address below to exercise these rights.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">9. Cookies</h2>
            <p>We use cookies and similar technologies to keep you signed in, remember your preferences (such as theme), and understand how the platform is used.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">10. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time. We will notify you of significant changes by email or through the platform.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">11. Contact Us</h2>
            <p>If you have questions about this Privacy Policy, please contact us at <a href="mailto:mcbankske@gmail.com">mcbankske@gmail.com</a>.</p>
        </div>
    </section>
@endsection
