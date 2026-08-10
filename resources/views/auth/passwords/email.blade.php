@extends('layouts.auth')

@section('content')
    <!-- ============================================================ -->
    <!-- PASSWORD RESET LAYOUT - Full width, no scroll                -->
    <!-- ============================================================ -->
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-grid" style="grid-template-columns: 1fr; align-content: center; justify-items: center;">

                <!-- Centered Reset Card -->
                <div class="reset-container">
                    <div class="reset-card">

                        <!-- Logo & Header -->
                        <div class="reset-logo">
                            <div class="logo-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <h2>Reset Your Password</h2>
                            <p>Enter your email address and we'll send you a link to reset your password.</p>
                        </div>

                        <!-- Status Messages -->
                        @if (session('status'))
                            <div class="reset-form">
                                <div class="status-success">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ session('status') }}
                                </div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="reset-form">
                                <div class="auth-error" style="background: #fef2f2; border: 1px solid #fca5a5; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                                    @foreach ($errors->all() as $error)
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 1rem; height: 1rem; flex-shrink: 0;">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $error }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Reset Form -->
                        <form class="reset-form" id="resetForm" method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="form-group">
                                <label for="resetEmail">Email Address</label>
                                <div class="input-wrapper">
                                    <div class="input-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input id="resetEmail"
                                           type="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           required
                                           autocomplete="email"
                                           autofocus
                                           class="auth-input @error('email') is-invalid @enderror"
                                           placeholder="Enter your email address">
                                </div>
                                @error('email')
                                    <div class="auth-error" id="resetEmailError">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="auth-error hidden" id="resetEmailClientError">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Email address is required
                                </div>
                            </div>

                            <button type="submit" class="btn-reset">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Send Password Reset Link
                            </button>

                            <!-- Success message (JS triggered) -->
                            <div class="auth-success hidden" id="resetSuccess" style="margin-top: 1rem;">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Password reset link sent! Please check your email.
                            </div>
                        </form>

                        <!-- Footer -->
                        <div class="reset-footer">
                            <a href="{{ route('login') }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Independent JS -->
    <script src="{{ asset('assets/js/auth.js') }}"></script>

@endsection