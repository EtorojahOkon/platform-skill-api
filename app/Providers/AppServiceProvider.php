<?php

namespace App\Providers;

use App\Listeners\GoalTrackingSubscriber;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
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
        $this->app->bind(
            \App\Contracts\GeminiInterface::class,
            \App\Services\V1\GeminiService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Event::subscribe(GoalTrackingSubscriber::class);

        JsonResource::withoutWrapping();

        $this->configureRoutes();

        $this->configurePasswords();

        $this->configureRateLimiting();

        $this->configureMorphAliases();
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

    protected function configureMorphAliases(): void
    {
        Relation::morphMap([
            'lesson' => 'App\Models\Lesson',
            'task' => 'App\Models\Task',
            'platform' => 'App\Models\Platform',
        ]);
    }
}
