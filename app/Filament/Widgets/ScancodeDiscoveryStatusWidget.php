<?php

namespace App\Filament\Widgets;

use App\Services\ScancodeDiscoveryProcessService;
use App\Services\ScancodeDiscoveryService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Throwable;

class ScancodeDiscoveryStatusWidget extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected static ?string $pollingInterval = '5s';

    protected string $view = 'filament.widgets.scancode-discovery-status-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $status = app(ScancodeDiscoveryProcessService::class)->status();
        $discovery = app(ScancodeDiscoveryService::class);

        return [
            'alias' => $status['alias'],
            'available' => $status['available'],
            'enabled' => (bool) config('nativephp.scancode_discovery.enabled', true),
            'host' => $discovery->host(),
            'pid' => $status['pid'],
            'port' => $discovery->port(),
            'running' => $status['running'],
        ];
    }

    public function restartScancodeDiscovery(): void
    {
        try {
            app(ScancodeDiscoveryProcessService::class)->restart();

            Notification::make()
                ->success()
                ->title(__('filamentphp-resources.widgets.scancode_discovery.notifications.restarted'))
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->danger()
                ->title(__('filamentphp-resources.widgets.scancode_discovery.notifications.restart_failed'))
                ->body($exception->getMessage())
                ->send();
        }
    }
}
