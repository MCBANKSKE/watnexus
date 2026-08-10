<?php

namespace App\Http\Middleware;

use App\Services\ApiKey\ApiKeyService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticate API requests via a company API key.
 *
 * The key is expected as `Authorization: Bearer {prefix}.{secret}`
 * or as the `X-API-Key` header.
 */
class AuthenticateApiKey
{
    public function __construct(
        protected ApiKeyService $apiKeyService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken()
            ?? $request->header('X-API-Key');

        if (!$token) {
            return ApiResponse::error('Missing API key.', 401);
        }

        $apiKey = $this->apiKeyService->resolve($token);

        if (!$apiKey) {
            return ApiResponse::error('Invalid or inactive API key.', 401);
        }

        if (!$this->apiKeyService->isIpAllowed($apiKey, $request->ip())) {
            return ApiResponse::error(
                'This API key is not allowed from your IP address.',
                403
            );
        }

        $this->apiKeyService->touchLastUsed($apiKey);

        // Expose the authenticated key + company for the rest of the request.
        $request->attributes->set('apiKey', $apiKey);
        $request->attributes->set('company', $apiKey->company);

        return $next($request);
    }
}