<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure an authenticated (non-super-admin) user belongs to an active
 * company. Otherwise force them through the company setup wizard.
 */
class CheckCompanySetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isSuperAdmin()) {
            $hasActiveCompany = $user->companies()
                ->wherePivot('is_active', true)
                ->exists();

            $allowedOnSetup = $request->routeIs([
                'company.setup.*',
                'logout',
                'verification.*',
                'password.*',
            ]);

            if (! $hasActiveCompany && ! $allowedOnSetup) {
                return redirect()->route('company.setup.index');
            }
        }

        return $next($request);
    }
}
