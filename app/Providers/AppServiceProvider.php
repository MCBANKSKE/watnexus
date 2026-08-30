<?php

namespace App\Providers;

use App\Support\ApiResponse;
use App\Support\CompanyContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CompanyContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the named rate limiters used by the API.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)
                ->by($this->limiterKey($request))
                ->response(function (Request $request, array $headers) {
                    return ApiResponse::error(
                        'Too many requests. Please slow down.',
                        429
                    )->withHeaders($headers);
                });
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(20)
                ->by($this->limiterKey($request))
                ->response(function (Request $request, array $headers) {
                    return ApiResponse::error(
                        'Too many requests. Please slow down.',
                        429
                    )->withHeaders($headers);
                });
        });
    }

    /**
     * Build a stable limiter key from the authenticated company or IP.
     */
    protected function limiterKey(Request $request): string
    {
        $company = $request->attributes->get('company');

        if ($company) {
            return 'company:'.$company->getKey();
        }

        return 'ip:'.($request->ip() ?? 'unknown');
    }
}
