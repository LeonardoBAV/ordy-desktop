<x-filament-widgets::widget>
    @once
        <style>
            .scancode-discovery-status-widget__card {
                background: rgb(24 24 27);
                border: 1px solid rgb(63 63 70);
                border-radius: 0.875rem;
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.18);
                min-width: 0;
                padding: 1.25rem;
            }

            .scancode-discovery-status-widget__label {
                color: rgb(161 161 170);
                font-size: 0.875rem;
                font-weight: 500;
                line-height: 1.25rem;
            }

            .scancode-discovery-status-widget__value {
                color: rgb(250 250 250);
                font-size: 1.5rem;
                font-weight: 600;
                line-height: 2rem;
                margin-top: 0.5rem;
            }

            .scancode-discovery-status-widget__description {
                color: rgb(161 161 170);
                font-size: 0.875rem;
                line-height: 1.25rem;
                margin-top: 0.5rem;
                overflow-wrap: anywhere;
            }

            .scancode-discovery-status-widget__value[data-status='success'] {
                color: rgb(74 222 128);
            }

            .scancode-discovery-status-widget__value[data-status='danger'] {
                color: rgb(248 113 113);
            }

            .scancode-discovery-status-widget__value[data-status='warning'] {
                color: rgb(250 204 21);
            }

            .scancode-discovery-status-widget__actions {
                margin-top: 1rem;
            }

            @media (prefers-color-scheme: light) {
                .scancode-discovery-status-widget__card {
                    background: rgb(255 255 255);
                    border-color: rgb(228 228 231);
                    box-shadow: 0 1px 2px rgb(0 0 0 / 0.06);
                }

                .scancode-discovery-status-widget__label,
                .scancode-discovery-status-widget__description {
                    color: rgb(113 113 122);
                }

                .scancode-discovery-status-widget__value {
                    color: rgb(24 24 27);
                }

                .scancode-discovery-status-widget__value[data-status='success'] {
                    color: rgb(22 163 74);
                }

                .scancode-discovery-status-widget__value[data-status='danger'] {
                    color: rgb(220 38 38);
                }

                .scancode-discovery-status-widget__value[data-status='warning'] {
                    color: rgb(202 138 4);
                }
            }
        </style>
    @endonce

    <div class="scancode-discovery-status-widget__card">
        <div class="scancode-discovery-status-widget__label">
            {{ __('filamentphp-resources.widgets.scancode_discovery.status.label') }}
        </div>

        @if (! $enabled)
            <div class="scancode-discovery-status-widget__value" data-status="warning">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.disabled') }}
            </div>

            <div class="scancode-discovery-status-widget__description">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.disabled_description') }}
            </div>
        @elseif (! $available)
            <div class="scancode-discovery-status-widget__value" data-status="warning">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.unavailable') }}
            </div>

            <div class="scancode-discovery-status-widget__description">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.unavailable_description', [
                    'host' => $host,
                    'port' => $port,
                ]) }}
            </div>
        @elseif ($running)
            <div class="scancode-discovery-status-widget__value" data-status="success">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.running') }}
            </div>

            <div class="scancode-discovery-status-widget__description">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.running_description', [
                    'alias' => $alias,
                    'host' => $host,
                    'pid' => $pid,
                    'port' => $port,
                ]) }}
            </div>
        @else
            <div class="scancode-discovery-status-widget__value" data-status="danger">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.stopped') }}
            </div>

            <div class="scancode-discovery-status-widget__description">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.stopped_description', [
                    'alias' => $alias,
                    'host' => $host,
                    'port' => $port,
                ]) }}
            </div>

            <div class="scancode-discovery-status-widget__description">
                {{ __('filamentphp-resources.widgets.scancode_discovery.status.stopped_hint') }}
            </div>
        @endif

        @if ($enabled && $available)
            <div class="scancode-discovery-status-widget__actions">
                <x-filament::button
                    color="gray"
                    size="sm"
                    wire:click="restartScancodeDiscovery"
                    wire:loading.attr="disabled"
                >
                    {{ __('filamentphp-resources.widgets.scancode_discovery.actions.restart') }}
                </x-filament::button>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
