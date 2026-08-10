@extends('layouts.auth')

@section('content')
    <!-- ============================================================ -->
    <!-- PASSWORD RESET CONFIRMATION - Full width, no scroll          -->
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <h2>Set New Password</h2>
                            <p>Please enter your new password below.</p>
                        </div>

                        <!-- Error Messages -->
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

                        <!-- Reset Confirmation Form -->
                        <form class="reset-form" id="resetConfirmForm" method="POST" action="{{ route('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

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
                                           value="{{ $email ?? old('email') }}" 
                                           required 
                                           autocomplete="email" 
                                           class="auth-input @error('email') is-invalid @enderror"
                                           placeholder="Enter your email address"
                                           readonly>
                                </div>
                                @error('email')
                                    <div class="auth-error">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="resetPassword">New Password *</label>
                                <div class="input-wrapper">
                                    <div class="input-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input id="resetPassword" 
                                           type="password" 
                                           name="password" 
                                           required 
                                           autocomplete="new-password"
                                           class="auth-input @error('password') is-invalid @enderror"
                                           placeholder="Enter new password">
                                </div>
                                @error('password')
                                    <div class="auth-error">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="auth-error hidden" id="resetPasswordError">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Password must be at least 8 characters
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="resetPasswordConfirm">Confirm Password *</label>
                                <div class="input-wrapper">
                                    <div class="input-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <input id="resetPasswordConfirm" 
                                           type="password" 
                                           name="password_confirmation" 
                                           required 
                                           autocomplete="new-password"
                                           class="auth-input"
                                           placeholder="Confirm new password">
                                </div>
                                <div class="auth-error hidden" id="resetPasswordConfirmError">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Passwords do not match
                                </div>
                            </div>

                            <button type="submit" class="btn-reset">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Reset Password
                            </button>
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

    <script>
        // Additional validation for reset confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const confirmForm = document.getElementById('resetConfirmForm');
            if (confirmForm) {
                confirmForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('resetPassword');
                    const confirm = document.getElementById('resetPasswordConfirm');
                    const passwordError = document.getElementById('resetPasswordError');
                    const confirmError = document.getElementById('resetPasswordConfirmError');

                    if (passwordError) passwordError.classList.add('hidden');
                    if (confirmError) confirmError.classList.add('hidden');

                    let isValid = true;

                    if (!password || password.value.length < 8) {
                        if (passwordError) passwordError.classList.remove('hidden');
                        isValid = false;
                    }

                    if (password && confirm && password.value !== confirm.value) {
                        if (confirmError) confirmError.classList.remove('hidden');
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
@endsection
