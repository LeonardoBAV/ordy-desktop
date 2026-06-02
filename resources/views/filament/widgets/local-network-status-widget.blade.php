<x-filament-widgets::widget>
    @once
        <style>
            .local-network-status-widget {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }

            @media (min-width: 768px) {
                .local-network-status-widget {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (min-width: 1280px) {
                .local-network-status-widget {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
            }

            .local-network-status-widget__card {
                background: rgb(24 24 27);
                border: 1px solid rgb(63 63 70);
                border-radius: 0.875rem;
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.18);
                min-width: 0;
                padding: 1.25rem;
            }

            .local-network-status-widget__label {
                color: rgb(161 161 170);
                font-size: 0.875rem;
                font-weight: 500;
                line-height: 1.25rem;
            }

            .local-network-status-widget__value {
                color: rgb(250 250 250);
                font-size: 1.5rem;
                font-weight: 600;
                line-height: 2rem;
                margin-top: 0.5rem;
                overflow-wrap: anywhere;
            }

            .local-network-status-widget__description {
                color: rgb(161 161 170);
                font-size: 0.875rem;
                line-height: 1.25rem;
                margin-top: 0.5rem;
                overflow-wrap: anywhere;
            }

            .local-network-status-widget__qr {
                align-items: center;
                background: rgb(255 255 255);
                border-radius: 0.75rem;
                display: flex;
                justify-content: center;
                margin-top: 1rem;
                padding: 0.75rem;
                width: fit-content;
            }

            .local-network-status-widget__qr img {
                display: block;
                height: 8rem;
                width: 8rem;
            }

            .local-network-status-widget__value--info {
                color: rgb(56 189 248);
            }

            .local-network-status-widget__value[data-status='success'] {
                color: rgb(74 222 128);
            }

            .local-network-status-widget__value[data-status='danger'] {
                color: rgb(248 113 113);
            }

            .local-network-status-widget__value[data-status='warning'] {
                color: rgb(250 204 21);
            }

            @media (prefers-color-scheme: light) {
                .local-network-status-widget__card {
                    background: rgb(255 255 255);
                    border-color: rgb(228 228 231);
                    box-shadow: 0 1px 2px rgb(0 0 0 / 0.06);
                }

                .local-network-status-widget__label,
                .local-network-status-widget__description {
                    color: rgb(113 113 122);
                }

                .local-network-status-widget__value {
                    color: rgb(24 24 27);
                }

                .local-network-status-widget__value--info {
                    color: rgb(2 132 199);
                }

                .local-network-status-widget__value[data-status='success'] {
                    color: rgb(22 163 74);
                }

                .local-network-status-widget__value[data-status='danger'] {
                    color: rgb(220 38 38);
                }

                .local-network-status-widget__value[data-status='warning'] {
                    color: rgb(202 138 4);
                }
            }
        </style>
    @endonce

    <div
        class="local-network-status-widget"
        x-data="{
            healthyUrl: @js($healthyUrl),
            status: @js($healthyUrl ? 'checking' : 'warning'),
            label: @js($healthyUrl ? __('filamentphp-resources.widgets.local_network.health.checking') : __('filamentphp-resources.widgets.local_network.health.failure')),
            description: @js($healthyUrl ? __('filamentphp-resources.widgets.local_network.health.checking_description', ['url' => $healthyUrl]) : __('filamentphp-resources.widgets.local_network.health.no_url')),
            messages: {
                success: @js(__('filamentphp-resources.widgets.local_network.health.success')),
                failure: @js(__('filamentphp-resources.widgets.local_network.health.failure')),
                requestSucceeded: @js(__('filamentphp-resources.widgets.local_network.health.request_succeeded', ['url' => $healthyUrl])),
                requestFailed: @js(__('filamentphp-resources.widgets.local_network.health.request_failed', ['url' => $healthyUrl])),
                unexpectedResponse: @js(__('filamentphp-resources.widgets.local_network.health.unexpected_response')),
            },
            async check() {
                if (! this.healthyUrl) {
                    return
                }

                try {
                    const response = await fetch(this.healthyUrl, {
                        cache: 'no-store',
                        headers: {
                            Accept: 'application/json',
                        },
                    })

                    const data = await response.json().catch(() => ({}))

                    if (response.ok && data.status === 'ok') {
                        this.status = 'success'
                        this.label = this.messages.success
                        this.description = this.messages.requestSucceeded

                        return
                    }

                    this.status = 'danger'
                    this.label = this.messages.failure
                    this.description = this.messages.unexpectedResponse.replace(':status', response.status)
                } catch (error) {
                    this.status = 'danger'
                    this.label = this.messages.failure
                    this.description = this.messages.requestFailed
                }
            },
        }"
        x-init="check()"
    >
        <div class="local-network-status-widget__card">
            <div class="local-network-status-widget__label">
                {{ __('filamentphp-resources.widgets.local_network.health.label') }}
            </div>

            <div
                class="local-network-status-widget__value"
                x-bind:data-status="status"
                x-text="label"
            ></div>

            <div class="local-network-status-widget__description" x-text="description"></div>
        </div>

        <div class="local-network-status-widget__card">
            <div class="local-network-status-widget__label">
                {{ __('filamentphp-resources.widgets.local_network.qr_code.label') }}
            </div>

            <div class="local-network-status-widget__description">
                {{ $baseUrl ?? __('filamentphp-resources.widgets.local_network.qr_code.unavailable') }}
            </div>

            @if ($qrCodeDataUri)
                <div class="local-network-status-widget__qr">
                    <img
                        src="{{ $qrCodeDataUri }}"
                        alt="{{ __('filamentphp-resources.widgets.local_network.qr_code.alt', ['url' => $baseUrl]) }}"
                    >
                </div>
            @else
                <div class="local-network-status-widget__value" data-status="warning">
                    {{ __('filamentphp-resources.widgets.local_network.qr_code.unavailable_short') }}
                </div>
            @endif
        </div>

        <div class="local-network-status-widget__card">
            <div class="local-network-status-widget__label">
                {{ __('filamentphp-resources.widgets.local_network.ip.label') }}
            </div>

            <div class="local-network-status-widget__value local-network-status-widget__value--info">
                {{ $localIp ?? __('filamentphp-resources.widgets.local_network.ip.unavailable') }}
            </div>

            <div class="local-network-status-widget__description">
                {{ $baseUrl ?? __('filamentphp-resources.widgets.local_network.ip.no_url') }}
            </div>
        </div>

        <div class="local-network-status-widget__card">
            <div class="local-network-status-widget__label">
                {{ __('filamentphp-resources.widgets.local_network.host.label') }}
            </div>

            <div class="local-network-status-widget__value">
                {{ $hostName }}
            </div>

            <div class="local-network-status-widget__description">
                {{ $hostDescription }}
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
