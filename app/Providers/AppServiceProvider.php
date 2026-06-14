<?php

namespace App\Providers;

use App\Services\ScancodeDiscoveryProcessService;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        $this->bootScancodeDiscovery();
    }

    private function bootScancodeDiscovery(): void
    {
        if (! config('nativephp-internal.running') || $this->app->runningInConsole()) {
            return;
        }

        try {
            app(ScancodeDiscoveryProcessService::class)->ensureRunning();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
