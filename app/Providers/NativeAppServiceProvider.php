<?php

namespace App\Providers;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Schema;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Screen;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $this->seedInitialUserWhenMissing();
        $this->openWindow();
    }

    private function seedInitialUserWhenMissing(): void
    {
        if (! config('nativephp-internal.running')) {
            return;
        }

        if (! Schema::hasTable('users') || User::query()->exists()) {
            return;
        }

        app(UserSeeder::class)->run();
    }

    private function openWindow(): void
    {
        $display = Screen::primary();
        $workArea = $display['workArea'] ?? $display['bounds'] ?? null;

        if ($workArea === null) {
            Window::open()
                ->showDevTools(false);

            return;
        }

        $width = (int) floor($workArea['width'] * 2 / 3);
        $height = (int) floor($workArea['height'] * 2 / 3);

        Window::open()
            ->width($width)
            ->height($height)
            ->position(
                (int) floor($workArea['x'] + (($workArea['width'] - $width) / 2)),
                (int) floor($workArea['y'] + (($workArea['height'] - $height) / 2)),
            )
            ->showDevTools(false);
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
