<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Log every API request for usage tracking and auditing.
 */
class LogApiRequests
{
    /**
     * Fields never persisted in request logs.
     *
     * @var list<string>
     */
    protected const SENSITIVE_FIELDS = [
        'password',
        'access_token',
        'token',
        'code',
        'api_key',
        'secret',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        $response = $next($request);

        $apiKey = $request->attributes->get('apiKey');
        $company = $request->attributes->get('company');

        $responseTimeMs = (int) round(
            (microtime(true) - $startedAt) * 1000
        );

        $statusCode = $response->getStatusCode();

        $payload = $this->responsePayload($response);

        ApiRequestLog::create([
            'company_id' => $company?->getKey(),
            'api_key_id' => $apiKey?->getKey(),
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'ip_address' => substr((string) $request->ip(), 0, 45),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'request_data' => $this->requestPayload($request),
            'response_data' => $payload,
            'error_message' => $statusCode >= 400
                ? ($payload['message'] ?? null)
                : null,
        ]);

        return $response;
    }

    /**
     * Sanitize the incoming request data.
     *
     * @return array<string, mixed>
     */
    protected function requestPayload(Request $request): array
    {
        $data = $request->except(self::SENSITIVE_FIELDS);

        return array_merge(
            $request->query(),
            is_array($data) ? $data : []
        );
    }

    /**
     * Capture a small representation of the JSON response.
     *
     * @return array<string, mixed>|null
     */
    protected function responsePayload(Response $response): ?array
    {
        if (!$response instanceof JsonResponse) {
            return null;
        }

        try {
            $decoded = json_decode((string) $response->getContent(), true);

            if (!is_array($decoded)) {
                return null;
            }

            // Strip heavy payloads before persisting.
            foreach (['data', 'errors'] as $key) {
                if (isset($decoded[$key]) && is_array($decoded[$key])) {
                    $decoded[$key] = array_slice($decoded[$key], 0, 20, true);
                }
            }

            return $decoded;
        } catch (Throwable $e) {
            Log::warning('Failed to capture API response payload', [
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }
}