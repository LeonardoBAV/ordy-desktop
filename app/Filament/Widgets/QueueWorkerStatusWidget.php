<?php

namespace App\Filament\Widgets;

use App\Services\QueueWorkerService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Throwable;

class QueueWorkerStatusWidget extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected static ?string $pollingInterval = '5s';

    protected string $view = 'filament.widgets.queue-worker-status-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $status = app(QueueWorkerService::class)->status();

        return [
            'available' => $status['available'],
            'running' => $status['running'],
            'pid' => $status['pid'],
            'alias' => $status['alias'],
        ];
    }

    public function restartQueueWorker(): void
    {
        try {
            app(QueueWorkerService::class)->restart();

            Notification::make()
                ->success()
                ->title(__('filamentphp-resources.widgets.queue_worker.notifications.restarted'))
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->danger()
                ->title(__('filamentphp-resources.widgets.queue_worker.notifications.restart_failed'))
                ->body($exception->getMessage())
                ->send();
        }
    }
}
