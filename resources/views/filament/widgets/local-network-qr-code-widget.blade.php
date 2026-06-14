<x-filament-widgets::widget>
    @once
        <style>
            .local-network-qr-code-widget__card {
                background: rgb(24 24 27);
                border: 1px solid rgb(63 63 70);
                border-radius: 0.875rem;
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.18);
                height: 100%;
                min-width: 0;
                padding: 1.25rem;
            }

            .local-network-qr-code-widget__label {
                color: rgb(161 161 170);
                font-size: 0.875rem;
                font-weight: 500;
                line-height: 1.25rem;
            }

            .local-network-qr-code-widget__value {
                color: rgb(250 250 250);
                font-size: 1.5rem;
                font-weight: 600;
                line-height: 2rem;
                margin-top: 0.5rem;
                overflow-wrap: anywhere;
            }

            .local-network-qr-code-widget__description {
                color: rgb(161 161 170);
                font-size: 0.875rem;
                line-height: 1.25rem;
                margin-top: 0.5rem;
                overflow-wrap: anywhere;
            }

            .local-network-qr-code-widget__qr {
                align-items: center;
                background: rgb(255 255 255);
                border-radius: 0.75rem;
                display: flex;
                justify-content: center;
                margin-top: 1rem;
                padding: 0.75rem;
                width: fit-content;
            }

            .local-network-qr-code-widget__qr img {
                display: block;
                height: 8rem;
                width: 8rem;
            }

            .local-network-qr-code-widget__value[data-status='warning'] {
                color: rgb(250 204 21);
            }

            @media (prefers-color-scheme: light) {
                .local-network-qr-code-widget__card {
                    background: rgb(255 255 255);
                    border-color: rgb(228 228 231);
                    box-shadow: 0 1px 2px rgb(0 0 0 / 0.06);
                }

                .local-network-qr-code-widget__label,
                .local-network-qr-code-widget__description {
                    color: rgb(113 113 122);
                }

                .local-network-qr-code-widget__value {
                    color: rgb(24 24 27);
                }

                .local-network-qr-code-widget__value[data-status='warning'] {
                    color: rgb(202 138 4);
                }
            }
        </style>
    @endonce

    <div class="local-network-qr-code-widget__card">
        <div class="local-network-qr-code-widget__label">
            {{ __('filamentphp-resources.widgets.local_network.qr_code.label') }}
        </div>

        <div class="local-network-qr-code-widget__description">
            {{ $baseUrl ?? __('filamentphp-resources.widgets.local_network.qr_code.unavailable') }}
        </div>

        @if ($qrCodeDataUri)
            <div class="local-network-qr-code-widget__qr">
                <img
                    src="{{ $qrCodeDataUri }}"
                    alt="{{ __('filamentphp-resources.widgets.local_network.qr_code.alt', ['url' => $baseUrl]) }}"
                >
            </div>
        @else
            <div class="local-network-qr-code-widget__value" data-status="warning">
                {{ __('filamentphp-resources.widgets.local_network.qr_code.unavailable_short') }}
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
