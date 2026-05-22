<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        $this->configureDefaults();
        $this->configureDownloadRateLimiter();
    }

    /**
     * Configure the download rate limiter for external MeuDanfe requests.
     */
    protected function configureDownloadRateLimiter(): void
    {
        RateLimiter::for('meudanfe-downloads', static function () {
            return Limit::perSecond(2)->by('meudanfe-downloads');
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        /*
        |--------------------------------------------------------------------------
        | HTTPS
        |--------------------------------------------------------------------------
        |
        | Necessário para:
        | - Railway
        | - Mercado Pago
        | - Google OAuth
        | - Sanctum
        | - Cookies secure
        |
        */

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        /*
        |--------------------------------------------------------------------------
        | Proteção contra comandos destrutivos
        |--------------------------------------------------------------------------
        */

        DB::prohibitDestructiveCommands(
            $this->app->isProduction(),
        );

        /*
        |--------------------------------------------------------------------------
        | Política de senha
        |--------------------------------------------------------------------------
        */

        Password::defaults(
            fn(): ?Password => $this->app->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }
}
