@extends('layouts.company-setup')

@section('title', 'Company Setup')

@section('content')
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-grid">

            <div class="brand-panel">
                <div class="flex-1">
                    <div class="brand-logo">
                        <div class="logo-icon">
                            <svg viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span class="logo-text">{{ config('app.name') }}</span>
                    </div>

                    <h1 class="brand-title">Welcome aboard</h1>
                    <p class="brand-subtitle">Two quick steps and you'll be up and running with Watnexus.</p>
                </div>

                <div class="setup-path">
                    <div class="progress-track">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>

                    <div class="setup-step is-active" data-wizard-step-target="1">
                        <div class="step-marker">1</div>
                        <div class="step-meta">
                            <span class="step-title">Company Details</span>
                            <span class="step-sub">Name, address &amp; business type</span>
                        </div>
                        <svg class="step-check" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>

                    <p class="path-note">You can change all of this later from the dashboard.</p>
                </div>
            </div>

            <div class="form-card form-card-register">
                <div class="form-header">
                    <span class="form-step-label">Step <b>1</b> of 1</span>
                    <h2>Company Details &amp; Address</h2>
                    <p>Tell us about your business — this appears on your documents and receipts.</p>
                </div>

                @if ($errors->any())
                    <div class="status-error cs-banner">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <span>Please fix the highlighted fields below and try again.</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="status-error cs-banner">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('company.setup.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-grid">
                        <div class="col-span-2">
                            <div class="form-group">
                                <label for="companyName">Company Name *</label>
                                <div class="input-wrapper">
                                    <input id="companyName" name="name" type="text" value="{{ old('name') }}" required
                                           class="auth-input @error('name') input-invalid @enderror"
                                           placeholder="e.g. Acme Traders Ltd">
                                </div>
                                @error('name')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="companyEmail">Company Email *</label>
                                <div class="input-wrapper">
                                    <input id="companyEmail" name="email" type="email" value="{{ old('email') ?? $user->email }}" required
                                           class="auth-input @error('email') input-invalid @enderror"
                                           placeholder="info@company.com">
                                </div>
                                @error('email')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="companyPhone">Phone *</label>
                                <div class="input-wrapper">
                                    <input id="companyPhone" name="phone" type="tel" value="{{ old('phone') }}" required
                                           class="auth-input @error('phone') input-invalid @enderror"
                                           placeholder="+254 700 000 000">
                                </div>
                                @error('phone')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="website">Website</label>
                                <div class="input-wrapper">
                                    <input id="website" name="website" type="url" value="{{ old('website') }}"
                                           class="auth-input @error('website') input-invalid @enderror"
                                           placeholder="https://www.company.com">
                                </div>
                                @error('website')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="registrationNumber">Registration Number</label>
                                <div class="input-wrapper">
                                    <input id="registrationNumber" name="registration_number" type="text" value="{{ old('registration_number') }}"
                                           class="auth-input @error('registration_number') input-invalid @enderror"
                                           placeholder="e.g. BN-12345678">
                                </div>
                                @error('registration_number')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="taxNumber">Tax Number</label>
                                <div class="input-wrapper">
                                    <input id="taxNumber" name="tax_number" type="text" value="{{ old('tax_number') }}"
                                           class="auth-input @error('tax_number') input-invalid @enderror"
                                           placeholder="e.g. A001234567P">
                                </div>
                                @error('tax_number')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-span-2">
                                <div class="form-group">
                                    <label for="logo_path">Company Logo</label>
                                    <div class="logo-dropzone @error('logo_path') input-invalid @enderror">
                                        <div class="logo-preview">
                                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                        </div>
                                        <div class="logo-copy">
                                            <div class="logo-title">Upload your logo</div>
                                            <div class="logo-sub">Drag a file here or click to browse (PNG or JPG)</div>
                                        </div>
                                        <button type="button" class="logo-clear">Remove</button>
                                        <input id="logo_path" name="logo_path" type="file" accept="image/*">
                                    </div>
                                    @error('logo_path')
                                        <div class="auth-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        <div class="col-span-2">
                            <div class="form-group">
                                <label for="address">Business Address *</label>
                                <div class="input-wrapper">
                                    <textarea id="address" name="address" rows="2" required
                                              class="auth-input @error('address') input-invalid @enderror"
                                              placeholder="123 Business Street, City, Country">{{ old('address') }}</textarea>
                                </div>
                                @error('address')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="country">Country *</label>
                                <div class="input-wrapper">
                                    <select id="country" name="country_id" required
                                            class="auth-input @error('country_id') input-invalid @enderror">
                                        <option value="">Select country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('country_id')
                                    <div class="auth-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        
                        
                    </div>

                    <div class="wizard-actions">
                        <button type="submit" class="cs-btn cs-btn-primary cs-btn-lg">Finish Setup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
