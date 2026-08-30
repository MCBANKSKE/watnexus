@extends('layouts.app')

@section('title', 'User Data Deletion — WatNexus')
@section('description', 'Instructions for requesting the deletion of your account and personal data from WatNexus, including data shared via Facebook Login.')

@section('content')
    <section class="legal-page" style="max-width: 800px; margin: 0 auto; padding: 64px 24px;">
        <h1 style="font-family: 'Fraunces', serif; font-size: 36px; margin-bottom: 8px;">User Data Deletion</h1>
        <p style="font-family: 'IBM Plex Mono', monospace; font-size: 13px; color: #6b7280; margin-bottom: 32px;">How to delete your WatNexus account and data</p>

        <div style="line-height: 1.8; color: #374151;">
            <h2 style="font-size: 22px; margin: 32px 0 12px;">1. Delete Your Data Through the App</h2>
            <p>You can delete your account and all associated data directly from within the WatNexus platform:</p>
            <ol>
                <li>Sign in to your WatNexus account.</li>
                <li>Go to <strong>Account Settings</strong> → <strong>Profile</strong>.</li>
                <li>Select <strong>Delete Account</strong>.</li>
                <li>Confirm the deletion when prompted.</li>
            </ol>
            <p>This permanently removes your account, company data, contacts, messages, campaigns, templates, and API keys from our systems.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">2. Delete Data Shared via Facebook / Meta Login</h2>
            <p>If you signed up or logged in using Facebook Login, you can also remove WatNexus from your connected apps:</p>
            <ol>
                <li>Open <a href="https://www.facebook.com/settings?tab=applications" target="_blank" rel="noopener">Facebook Settings &gt; Apps and Websites</a>.</li>
                <li>Find <strong>WatNexus</strong> in the list.</li>
                <li>Click <strong>Remove</strong>. This revokes our access and signals us to delete your data.</li>
            </ol>
            <p>Once removed, we delete the information we received from Facebook (such as your name and email address) within 30 days.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">3. Request Deletion by Email</h2>
            <p>If you cannot access your account, you can request deletion by contacting us directly:</p>
            <ul>
                <li>Email: <a href="mailto:mcbankske@gmail.com?subject=Data%20Deletion%20Request">mcbankske@gmail.com</a></li>
                <li>Subject line: <strong>Data Deletion Request</strong></li>
                <li>Include the email address associated with your account so we can verify your identity.</li>
            </ul>
            <p>We will process verified deletion requests within 30 days.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">4. What Data Is Deleted</h2>
            <ul>
                <li>Account details (name, email, password hash, profile information)</li>
                <li>Company and business setup information</li>
                <li>Contacts, contact lists, messages, campaigns, and message templates</li>
                <li>Connected WhatsApp Business accounts, phone numbers, and encrypted access tokens</li>
                <li>API keys, webhook configurations, and API request logs</li>
            </ul>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">5. Data We May Retain</h2>
            <p>Certain records may be retained where required by law (such as usage and billing records) or for legitimate security purposes. Retained data is minimized and kept only as long as necessary, as described in our <a href="{{ route('privacy-policy') }}">Privacy Policy</a>.</p>

            <h2 style="font-size: 22px; margin: 32px 0 12px;">6. Contact Us</h2>
            <p>Questions about data deletion? Contact us at <a href="mailto:mcbankske@gmail.com">mcbankske@gmail.com</a>.</p>
        </div>
    </section>
@endsection
