<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Facades\Activity;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

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
        Schema::defaultStringLength(191);

        // When model traits log activities, Spatie does not resolve the causer
        // automatically. Inject the currently authenticated user as the causer
        // so model-logged events (transactions/categories) are attributed.
        Activity::beforeLogging(function (SpatieActivity $activity) {
            if ($activity->causer_id === null) {
                $user = Auth::user();
                if ($user) {
                    $activity->causer()->associate($user);
                }
            }

            $request = request();
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            if ($ipAddress !== null || $userAgent !== null) {
                $properties = $activity->properties ?? collect();

                if ($ipAddress !== null) {
                    $properties->put('ip_address', $ipAddress);
                }

                if ($userAgent !== null) {
                    $properties->put('user_agent', $userAgent);
                }

                $activity->properties = $properties;
            }
        });
    }
}
