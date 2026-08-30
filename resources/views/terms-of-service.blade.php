@extends('layouts.app')

@section('title', 'Terms of Service — WatNexus')
@section('description', 'The terms and conditions governing your use of the WatNexus WhatsApp Business messaging platform.')

@section('content')
    <section class="legal-page" style="max-width: 800px; margin: 0 auto; padding: 64px 24px;">
        <h1 style="font-family: 'Fraunces', serif; font-size: 36px; margin-bottom: 8px;">Terms of Service</h1>
        <p style="font-family: 'IBM Plex Mono', monospace; font-size: 13px; color: #6b7280; margin-bottom: 32px;">Last updated: {{ now()->format('F j, Y') }}</p>

        <div style="line-height: 1.8; color: #374151;">
            <h2 style="font-size: 22px; margin: 32px 0 12px;">1. Acceptance of Terms</h2>
            <p>By accessing or using WatNexus ("the Service"), you agree to be bound by these Terms of Service. If you do not agree, do not use the Service.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">2. Description of the Service</h2>
            <p>WatNexus is a multi-tenant WhatsApp Business API platform that enables businesses to connect their WhatsApp Business accounts and send text messages, media, template messages, and OTP verification codes, manage contacts, and run marketing campaigns through a unified REST API.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">3. Accounts and Eligibility</h2>
            <ul>
                <li>You must be at least 18 years old and legally able to enter into binding contracts.</li>
                <li>You are responsible for maintaining the confidentiality of your account credentials and API keys.</li>
                <li>You are responsible for all activity that occurs under your account and API keys.</li>
            </ul>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">4. Acceptable Use</h2>
            <p>You agree to use the Service in compliance with the <strong>WhatsApp Business Policy</strong> and all applicable laws. You must not use the Service to send spam, unsolicited bulk messages, phishing content, or unlawful material. You may not attempt to interfere with, disrupt, or gain unauthorized access to the Service or related systems.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">5. API Keys and Security</h2>
            <ul>
                <li>API keys are confidential; do not share or expose them publicly.</li>
                <li>We provide permission-based access control, optional IP whitelisting, and rate limiting per key.</li>
                <li>Report compromised API keys immediately so we can revoke them.</li>
            </ul>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">6. WhatsApp and Meta Requirements</h2>
            <p>Your use of the Service is also subject to Meta's WhatsApp Business Terms of Service and Business Policy. We are not responsible for message rejections, template approvals, account restrictions, or phone number bans imposed by Meta.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">7. Fees and Usage</h2>
            <ul>
                <li>We may charge fees based on usage or plan; applicable fees are disclosed before you are billed.</li>
                <li>Meta may charge per-message or conversation fees for WhatsApp messages; those are separate from our fees.</li>
                <li>All fees are non-refundable except where required by law or stated otherwise.</li>
            </ul>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">8. Your Content</h2>
            <p>You retain ownership of your contacts, message templates, campaign content, and other data you create. You grant us a limited license to store and process that content solely to provide the Service.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">9. Service Availability</h2>
            <p>We aim to keep the Service available but do not guarantee uninterrupted or error-free operation. We may modify, suspend, or discontinue any part of the Service with reasonable notice.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">10. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, WatNexus shall not be liable for indirect, incidental, special, or consequential damages, including lost profits or data, arising from your use of the Service.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">11. Termination</h2>
            <p>We may suspend or terminate your account if you violate these Terms or the WhatsApp Business Policy. You may stop using the Service and delete your account at any time.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">12. Governing Law</h2>
            <p>These Terms are governed by the laws of the jurisdiction in which WatNexus operates, without regard to conflict-of-law principles.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">13. Changes to These Terms</h2>
            <p>We may update these Terms from time to time. Continued use of the Service after changes take effect constitutes acceptance of the new Terms.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">14. Contact Us</h2>
            <p>Questions about these Terms? Contact us at <a href="mailto:mcbankske@gmail.com">mcbankske@gmail.com</a>.</p>
        </div>
    </section>
@endsection
