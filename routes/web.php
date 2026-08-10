<?php

use App\Http\Controllers\WhatsApp\WhatsAppAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('whatsapp')->group(function () {
    Route::get(
        '/auth/redirect',
        [WhatsAppAuthController::class, 'redirect']
    )->name('whatsapp.auth.redirect');

    Route::get(
        '/auth/callback',
        [WhatsAppAuthController::class, 'callback']
    )->name('whatsapp.auth.callback');
});
