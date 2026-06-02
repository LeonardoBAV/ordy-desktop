<?php

namespace App\Filament\Widgets;

use App\Services\LocalNetworkService;
use Filament\Widgets\Widget;

class LocalNetworkStatusWidget extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.local-network-status-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $localNetworkService = app(LocalNetworkService::class);
        $baseUrl = $localNetworkService->baseUrl();

        return [
            'baseUrl' => $baseUrl,
            'healthyUrl' => $baseUrl ? "{$baseUrl}/api/healthy" : null,
            'hostDescription' => $this->hostDescription($localNetworkService),
            'hostName' => gethostname() ?: __('filamentphp-resources.widgets.local_network.host.unknown'),
            'localIp' => $localNetworkService->localIp(),
        ];
    }

    private function hostDescription(LocalNetworkService $localNetworkService): string
    {
        return __('filamentphp-resources.widgets.local_network.host.description', [
            'os' => PHP_OS_FAMILY,
            'arch' => php_uname('m'),
            'port' => $localNetworkService->port(),
        ]);
    }
}
