<?php

namespace App\Http\Middleware;

use App\Services\ApiKey\ApiKeyService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce a required permission for an API key.
 *
 * Usage: `->middleware('api.key.permission:messages.send')`
 */
class EnsureApiKeyPermission
{
    public function __construct(
        protected ApiKeyService $apiKeyService
    ) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $apiKey = $request->attributes->get('apiKey');

        if (! $apiKey || ! $this->apiKeyService->hasPermission($apiKey, $permission)) {
            return ApiResponse::error(
                'This API key does not have the required permission.',
                403
            );
        }

        return $next($request);
    }
}
