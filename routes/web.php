<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CompanySetupController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WhatsAppQrCodeController;
use App\Http\Controllers\WhatsAppOAuthController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms-of-service');

Route::get('/data-deletion', function () {
    return view('data-deletion');
})->name('data-deletion');


// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();

    $user = $request->user();

    // Admins (and other staff who completed company setup) should land in their
    // admin panel after verifying. The /customer fallback is kept for future
    // customer-facing workflows.
    if ($user->hasRole('admin') || $user->hasRole('pending_company_setup') || $user->is_superadmin) {
        return redirect()->route('filament.admin.pages.dashboard');
    }

    return redirect('/customer');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
        // Google authentication (sign in & sign up with a Google account)
    Route::get('login/google', [SocialAuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('login/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('login.google.callback');

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

    Route::get('search/companies', [SearchController::class, 'companies'])->name('search.companies');
    Route::get('search/countries', [SearchController::class, 'countries'])->name('search.countries');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Company Setup Routes (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/company-setup', [CompanySetupController::class, 'showCompanySetupForm'])
        ->name('company.setup');
    Route::post('/company-setup', [CompanySetupController::class, 'storeCompanySetup'])
        ->name('company.setup.store');
});
Route::post('/admin/logout', function (Request $request) {
    Auth::logout();
    return redirect('/login');
})->name('filament.admin.auth.logout');
Route::post('/customer/logout', function (Request $request) {
    Auth::logout();
    return redirect('/login');
})->name('filament.customer.auth.logout');
Route::post('/super-admin/logout', function (Request $request) {
    Auth::logout();
    return redirect('/login');
})->name('filament.super-admin.auth.logout');

// Redirect panel login routes to central login
Route::get('/admin/login', fn () => redirect('/login'))->name('filament.admin.auth.login');
Route::get('/customer/login', fn () => redirect('/login'))->name('filament.customer.auth.login');
Route::get('/super-admin/login', fn () => redirect('/login'))->name('filament.super-admin.auth.login');

/*
| WhatsApp onboarding.
*/

Route::prefix('whatsapp')->group(function () {
    Route::get('/auth/redirect', [WhatsAppAuthController::class, 'redirect'])
        ->name('whatsapp.auth.redirect');

    Route::get('/auth/callback', [WhatsAppAuthController::class, 'callback'])
        ->name('whatsapp.auth.callback');

    // QR Code Connection Routes
    Route::get('/qr/generate', [WhatsAppQrCodeController::class, 'generate'])
        ->name('whatsapp.qr.generate');
    
    Route::get('/qr/status', [WhatsAppQrCodeController::class, 'checkStatus'])
        ->name('whatsapp.qr.status');
    
    Route::post('/qr/callback', [WhatsAppQrCodeController::class, 'callback'])
        ->name('whatsapp.qr.callback');

    // OAuth Connection Routes
    Route::get('/oauth/authorize', [WhatsAppOAuthController::class, 'authorize'])
        ->name('whatsapp.oauth.authorize');
    
    Route::post('/oauth/callback', [WhatsAppOAuthController::class, 'callback'])
        ->name('whatsapp.oauth.callback');
    
    Route::post('/oauth/refresh', [WhatsAppOAuthController::class, 'refresh'])
        ->name('whatsapp.oauth.refresh');
});
