<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Base controller for public API endpoints.
 */
abstract class ApiController extends Controller
{
    /**
     * Company authenticated via the API key.
     */
    protected function company(Request $request): Company
    {
        return $request->attributes->get('company');
    }

    /**
     * API key that authenticated the request.
     */
    protected function apiKey(Request $request): ApiKey
    {
        return $request->attributes->get('apiKey');
    }
}