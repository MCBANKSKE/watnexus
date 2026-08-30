@php
    /**
     * Shared "Continue with Google" control.
     *
     * Place inside any auth <form> (login or register). It links to the
     * SocialAuthController OAuth redirect; the callback handles both
     * first-time sign-up (-> /company-setup, like normal registration) and
     * returning-user sign-in.
     */
@endphp

<div class="auth-divider"><span>or</span></div>
<a href="{{ route('login.google') }}" class="btn-google wide" rel="noopener">
    <svg width="24" height="24" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path fill="#4285F4" d="M22 .5C11.7.5.5 11.7.5 22S11.7 43.5 22 43.5 43.5 32.3 43.5 22 32.3.5 22 .5Zm8.9 27.9L30 34.5c-1.3 1.1-3 2-4.9 2.5-.1 0-.1.1-.2.1.1 0 .1.1 0 .1A15.5 15.5 0 0 1 9.5 22c0-3.7 1.3-7 3.3-9.5l1.4 4.6A9 9 0 0 1 22 32c1.5 0 2.9-.4 4.1-1.1.3-.1.6-.3.9-.4Z"/>
        <path fill="#34A853" d="M22 .5C11.7.5.5 11.7.5 22S11.7 43.5 22 43.5V32c-1.8.4-3.5.5-5.2-.2a15.5 15.5 0 0 1 0-29.2c3.7 0 7 1.5 9.6 3.9l3.2-3.2A15.4 15.4 0 0 0 22 .5Z"/>
        <path fill="#FBBC05" d="M36.5 22a15.4 15.4 0 0 1-5.2 10.3l-3.2-3.2A9 9 0 0 1 32 22c0-2.8-.8-5.4-2.2-7.6l2.5-2.5a15.4 15.4 0 0 1 4.2 12.1Z"/>
        <path fill="#EA4335" d="M15.2 8.3a9 9 0 0 1 5-2.8 9 9 0 0 1 8.7 6.2l3.2-3.2A15.5 15.5 0 0 0 9.5 22c0 3.4 1.2 6.5 3.3 9l-3.6 4.2a15.5 15.5 0 0 1 0-26.9Z"/>
    </svg>
    <span>Continue with Google</span>
</a>

<style>
    .auth-divider { display:flex;align-items:center;text-align:center;margin:1rem 0;color:#6b7280;font-size:.875rem; }
    .auth-divider::before,.auth-divider::after { content:''; flex:1;border-top:1px solid #e5e7eb; }
    .auth-divider span { padding:0 .625rem; }
    .btn-google { display:inline-flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:.6rem 1rem;border:1px solid #dadce0;border-radius:8px;background:#ffffff;color:#3c4043;font-weight:500;text-decoration:none;transition:.15s background-color,.15s box-shadow; }
    .btn-google:hover { background:#f7f8f8;box-shadow:0 1px 3px rgba(0,0,0,.08); }
    .btn-google:focus-visible { outline:2px solid #4285f4;outline-offset:1px; }
</style>
