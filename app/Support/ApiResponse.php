<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Standardized JSON responses for the public API.
 */
class ApiResponse
{
    /**
     * Success response with a payload.
     */
    public static function data(
        mixed $data,
        ?string $message = null,
        int $status = 200,
        array $headers = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status)->withHeaders($headers);
    }

    /**
     * Success response with only a message.
     */
    public static function message(string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => null,
        ], $status);
    }

    /**
     * Error response.
     *
     * @param array<string, mixed>|null $errors Field-level validation errors.
     */
    public static function error(
        string $message,
        int $status = 400,
        ?array $errors = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Validation error response (422).
     *
     * @param array<string, mixed> $errors
     */
    public static function validation(array $errors): JsonResponse
    {
        return self::error('The given data was invalid.', 422, $errors);
    }
}