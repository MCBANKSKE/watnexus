@extends('layouts.app')

@section('title', 'Connect WhatsApp')

@section('content')
    <div class="wa-connect">
        <div class="wa-connect__header">
            <p class="wa-connect__eyebrow">WHATSAPP CONNECTION</p>
            <h1>Connect WhatsApp without the technical setup.</h1>
            <p>Choose the method that works for your business. We will securely save the connection and sync your phone numbers automatically.</p>
        </div>

        @if (session('oauth_error'))
            <div class="wa-connect__alert wa-connect__alert--error">{{ session('oauth_error') }}</div>
        @endif
        @if (session('oauth_success'))
            <div class="wa-connect__alert wa-connect__alert--success">{{ session('oauth_success') }}</div>
        @endif

        <div class="wa-connect__options">
            <article class="wa-connect__card wa-connect__card--featured">
                <span class="wa-connect__badge">RECOMMENDED</span>
                <h2>Continue with Meta</h2>
                <p>Sign in to Meta and choose your WhatsApp Business account. No WABA ID or access token required.</p>
                <a class="wa-connect__button" href="{{ route('whatsapp.oauth.authorize') }}">Connect with Meta <span aria-hidden="true">→</span></a>
                @unless ($metaConfigured)
                    <p class="wa-connect__notice">Meta sign-in needs to be configured by your administrator first.</p>
                @endunless
            </article>

            <article class="wa-connect__card">
                <h2>Scan a QR code</h2>
                <p>Use your WhatsApp Business app to scan a secure, short-lived code and connect your account.</p>
                <button class="wa-connect__button wa-connect__button--secondary" type="button" id="generate-qr" {{ $qrConfigured ? '' : 'disabled' }}>Show QR code</button>
                <div id="qr-result" class="wa-connect__qr" hidden aria-live="polite"></div>
                @unless ($qrConfigured)
                    <p class="wa-connect__notice">QR connection needs to be configured by your administrator first.</p>
                @endunless
            </article>

            <article class="wa-connect__card wa-connect__card--manual">
                <h2>Enter details manually</h2>
                <p>Already have a WABA ID and system-user token? Use the advanced connection form instead.</p>
                <a class="wa-connect__text-link" href="{{ $manualUrl }}">Use manual setup <span aria-hidden="true">→</span></a>
            </article>
        </div>
    </div>

    <style>
        .wa-connect { max-width: 1080px; margin: 56px auto 80px; padding: 0 24px; color: #172033; }
        .wa-connect__header { max-width: 680px; margin-bottom: 32px; }
        .wa-connect__eyebrow { color: #087f5b; font-size: .75rem; font-weight: 800; letter-spacing: .12em; margin: 0 0 10px; }
        .wa-connect h1 { font-size: clamp(2rem, 5vw, 3.25rem); letter-spacing: -.045em; line-height: 1.08; margin: 0 0 14px; }
        .wa-connect__header > p:last-child { color: #5e6879; font-size: 1.08rem; line-height: 1.6; }
        .wa-connect__options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .wa-connect__card { background: #fff; border: 1px solid #e2e7ee; border-radius: 18px; display: flex; flex-direction: column; min-height: 280px; padding: 27px; box-shadow: 0 8px 24px rgba(20, 33, 55, .05); }
        .wa-connect__card--featured { border: 2px solid #25d366; position: relative; }
        .wa-connect__badge { align-self: flex-start; background: #e6f9ee; border-radius: 20px; color: #087f5b; font-size: .68rem; font-weight: 800; letter-spacing: .08em; padding: 5px 9px; }
        .wa-connect h2 { font-size: 1.25rem; margin: 18px 0 9px; }
        .wa-connect__card p { color: #5e6879; line-height: 1.55; margin: 0; }
        .wa-connect__button { align-items: center; background: #087f5b; border: 0; border-radius: 9px; color: #fff; cursor: pointer; display: flex; font-weight: 700; gap: 12px; justify-content: center; margin-top: auto; padding: 12px 16px; text-decoration: none; }
        .wa-connect__button--secondary { background: #eef8f3; color: #087f5b; width: 100%; }
        .wa-connect__button:disabled { cursor: not-allowed; opacity: .55; }
        .wa-connect__text-link { color: #087f5b; font-weight: 700; margin-top: auto; text-decoration: none; }
        .wa-connect__notice { color: #8a5a00 !important; font-size: .83rem; margin-top: 12px !important; }
        .wa-connect__alert { border-radius: 9px; margin: 0 0 20px; padding: 12px 15px; }
        .wa-connect__alert--error { background: #fff1f2; color: #a61b32; }.wa-connect__alert--success { background: #ecfdf3; color: #087f5b; }
        .wa-connect__qr { margin-top: 18px; text-align: center; }.wa-connect__qr img { max-width: 190px; }.wa-connect__qr p { font-size: .85rem; margin-top: 8px; }
        @media (max-width: 800px) { .wa-connect__options { grid-template-columns: 1fr; }.wa-connect__card { min-height: 220px; } }
    </style>

    <script>
        document.getElementById('generate-qr')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            const result = document.getElementById('qr-result');
            button.disabled = true;
            button.textContent = 'Creating secure code…';

            try {
                const response = await fetch('{{ route('whatsapp.qr.generate') }}', { headers: { Accept: 'application/json' } });
                const payload = await response.json();
                if (!response.ok || !payload.success) throw new Error(payload.message || 'Unable to create a QR code.');
                const image = document.createElement('img');
                image.src = payload.data.qr_code;
                image.alt = 'WhatsApp connection QR code';
                const message = document.createElement('p');
                message.textContent = 'Scan this code before it expires.';
                result.replaceChildren(image, message);
                result.hidden = false;
                button.hidden = true;
            } catch (error) {
                result.textContent = error.message;
                result.hidden = false;
                button.disabled = false;
                button.textContent = 'Try again';
            }
        });
    </script>
@endsection
