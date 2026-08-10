<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Domain exception rendered as a standardized API error.
 */
class ApiException extends Exception
{
    public array $errors;

    public function __construct(
        string $message,
        int $status = 400,
        array $errors = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $status, $previous);

        $this->errors = $errors;
    }

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error(
            $this->getMessage(),
            $this->getCode() ?: 400,
            $this->errors ?: null
        );
    }
}