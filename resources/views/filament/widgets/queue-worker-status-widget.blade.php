<x-filament-widgets::widget>
    @once
        <style>
            .queue-worker-status-widget__card {
                background: rgb(24 24 27);
                border: 1px solid rgb(63 63 70);
                border-radius: 0.875rem;
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.18);
                min-width: 0;
                padding: 1.25rem;
            }

            .queue-worker-status-widget__label {
                color: rgb(161 161 170);
                font-size: 0.875rem;
                font-weight: 500;
                line-height: 1.25rem;
            }

            .queue-worker-status-widget__value {
                color: rgb(250 250 250);
                font-size: 1.5rem;
                font-weight: 600;
                line-height: 2rem;
                margin-top: 0.5rem;
            }

            .queue-worker-status-widget__description {
                color: rgb(161 161 170);
                font-size: 0.875rem;
                line-height: 1.25rem;
                margin-top: 0.5rem;
            }

            .queue-worker-status-widget__value[data-status='success'] {
                color: rgb(74 222 128);
            }

            .queue-worker-status-widget__value[data-status='danger'] {
                color: rgb(248 113 113);
            }

            .queue-worker-status-widget__value[data-status='warning'] {
                color: rgb(250 204 21);
            }

            .queue-worker-status-widget__actions {
                margin-top: 1rem;
            }

            @media (prefers-color-scheme: light) {
                .queue-worker-status-widget__card {
                    background: rgb(255 255 255);
                    border-color: rgb(228 228 231);
                    box-shadow: 0 1px 2px rgb(0 0 0 / 0.06);
                }

                .queue-worker-status-widget__label,
                .queue-worker-status-widget__description {
                    color: rgb(113 113 122);
                }

                .queue-worker-status-widget__value {
                    color: rgb(24 24 27);
                }

                .queue-worker-status-widget__value[data-status='success'] {
                    color: rgb(22 163 74);
                }

                .queue-worker-status-widget__value[data-status='danger'] {
                    color: rgb(220 38 38);
                }

                .queue-worker-status-widget__value[data-status='warning'] {
                    color: rgb(202 138 4);
                }
            }
        </style>
    @endonce

    <div class="queue-worker-status-widget__card">
        <div class="queue-worker-status-widget__label">
            {{ __('filamentphp-resources.widgets.queue_worker.status.label') }}
        </div>

        @if (! $available)
            <div class="queue-worker-status-widget__value" data-status="warning">
                {{ __('filamentphp-resources.widgets.queue_worker.status.unavailable') }}
            </div>

            <div class="queue-worker-status-widget__description">
                {{ __('filamentphp-resources.widgets.queue_worker.status.unavailable_description') }}
            </div>
        @elseif ($running)
            <div class="queue-worker-status-widget__value" data-status="success">
                {{ __('filamentphp-resources.widgets.queue_worker.status.running') }}
            </div>

            <div class="queue-worker-status-widget__description">
                {{ __('filamentphp-resources.widgets.queue_worker.status.running_description', [
                    'alias' => $alias,
                    'pid' => $pid,
                ]) }}
            </div>
        @else
            <div class="queue-worker-status-widget__value" data-status="danger">
                {{ __('filamentphp-resources.widgets.queue_worker.status.stopped') }}
            </div>

            <div class="queue-worker-status-widget__description">
                {{ __('filamentphp-resources.widgets.queue_worker.status.stopped_description', [
                    'alias' => $alias,
                ]) }}
            </div>
        @endif

        @if ($available)
            <div class="queue-worker-status-widget__actions">
                <x-filament::button
                    color="gray"
                    size="sm"
                    wire:click="restartQueueWorker"
                    wire:loading.attr="disabled"
                >
                    {{ __('filamentphp-resources.widgets.queue_worker.actions.restart') }}
                </x-filament::button>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
