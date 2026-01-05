<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\LogSuccessfulLogin;

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
        Vite::prefetch(concurrency: 3);
        Event::listen(Login::class, LogSuccessfulLogin::class);
        
        // Log de queries SQL lentas (solo en desarrollo o cuando se active)
        if (config('app.debug') || env('LOG_SQL_QUERIES', false)) {
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                // Solo log queries que tomen más de 1 segundo
                if ($query->time > 1000) {
                    \App\Helpers\LogHelper::queries()->warning('Query lenta detectada', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms' => $query->time,
                    ]);
                } else {
                    // Log todas las queries en modo debug
                    \App\Helpers\LogHelper::queries()->debug('Query ejecutada', [
                        'sql' => $query->sql,
                        'time_ms' => $query->time,
                    ]);
                }
            });
        }
    }
}
