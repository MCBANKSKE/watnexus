<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\CheckCompanySetup;
use App\Http\Middleware\EnsureApiKeyPermission;
use App\Http\Middleware\LogApiRequests;
use App\Http\Middleware\VerifyRole;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.apikey' => AuthenticateApiKey::class,
            'log.api' => LogApiRequests::class,
            'api.key.permission' => EnsureApiKeyPermission::class,
            'role' => VerifyRole::class,
            'company.setup' => CheckCompanySetup::class,
        ]);

        $middleware->api(append: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->web(append: [
            AuthenticateSession::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
        ]);

        // Meta sends POST webhooks without a CSRF token.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ApiException $e, Request $request) {
            if ($request->is('api/*')) {
                return $e->render($request);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::validation($e->errors());
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Resource not found.', 404);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Unauthenticated.', 401);
            }
        });
    })->create();
