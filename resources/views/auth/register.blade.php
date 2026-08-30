@extends('layouts.auth')

@section('title', 'Register — Nexus Invoicing')

@section('content')
<div class="auth-container is-signup" id="authContainer">

    <div class="auth-panel panel-signin">
        <div class="auth-form">
            <h2>Welcome back</h2>
            <div class="auth-sub">Log in to your Nexus workspace.</div>

            @if (session('status'))
                <div class="auth-status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <div class="input-group">
                        <svg viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
                    </div>
                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <div class="input-group has-toggle">
                        <svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
                        <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false">
                            <svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><path d="M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-row-between">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-primary wide">Sign in →</button>

                @include('auth.partials.google_button')
            </form>

            <div class="auth-switch">
                Don't have an account? <button type="button" onclick="toggleAuth('signup')">Sign up here</button>
            </div>
        </div>
    </div>

    <div class="auth-panel panel-signup">
        <div class="auth-form">
            <h2>Create your account</h2>
            <div class="auth-sub">Set up access before configuring your company.</div>

            @if (session('status'))
                <div class="auth-status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="field">
                    <div class="input-group">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0116 0v1"/></svg>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name" required autofocus>
                    </div>
                    @error('name')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <div class="input-group">
                        <svg viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
                    </div>
                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <div class="input-group has-toggle">
                        <svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        <input type="password" name="password" placeholder="Password" required autocomplete="new-password">
                        <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false">
                            <svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><path d="M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <div class="input-group has-toggle">
                        <svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        <input type="password" name="password_confirmation" placeholder="Confirm Password" required autocomplete="new-password">
                        <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false">
                            <svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><path d="M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-primary wide">Create account →</button>

                @include('auth.partials.google_button')
            </form>

            <div class="auth-switch">
                Already have an account? <button type="button" onclick="toggleAuth('signin')">Sign in here</button>
            </div>
        </div>
    </div>

    <div class="auth-overlay">
        <div class="overlay-content overlay-signup">
            <h3>New to Nexus?</h3>
            <p>Register your company and start quoting, invoicing, and getting paid on M-Pesa in minutes.</p>
            <button type="button" onclick="toggleAuth('signup')">Create account</button>
        </div>
        <div class="overlay-content overlay-signin">
            <h3>Already with us?</h3>
            <p>Sign in to pick up your quotes, invoices, and payments right where you left off.</p>
            <button type="button" onclick="toggleAuth('signin')">Sign in</button>
        </div>
    </div>

</div>
@endsection
