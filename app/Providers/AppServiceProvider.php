<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addHour(2));
        Passport::refreshTokensExpireIn(now()->addMinutes(2));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        // Passport::tokensExpireIn(now()->addHours(2));
        // Passport::refreshTokensExpireIn(now()->addDays(30));
        // Passport::personalAccessTokensExpireIn(Carbon::now()->addMinutes(15));
        // Passport::loadKeysFrom(__DIR__ . '/../secrets/oauth');
    }
}
