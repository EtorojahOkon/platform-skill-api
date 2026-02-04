<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        $this->configureRoutes();

        $this->configurePasswords();

        $this->configureRateLimiting();
    }

    protected function configureRoutes(): void
    {
        $routeFiles = glob(base_path('routes/v1/*.php'));

        foreach ($routeFiles as $file) {
            Route::prefix('v1')
                ->middleware('api')
                ->namespace('App\Http\Controllers\V1')
                ->group($file);
        }
    }

    protected function configurePasswords(): void
    {
        Password::defaults(function () {
            return app()->environment('production')
                ? Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : Password::min(8);
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(1)->by($request->ip());
        });

        RateLimiter::for('email', function (Request $request) {
            return Limit::perMinute(2)->by($request->ip());
        });
    }
}
