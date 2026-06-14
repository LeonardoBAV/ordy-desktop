<?php

namespace App\Services;

class ScancodeDiscoveryService
{
    public const RequestPayload = 'SCANCODE_DISCOVERY_REQUEST';

    public function __construct(
        private LocalNetworkService $localNetworkService,
    ) {}

    public function host(): string
    {
        $host = config('nativephp.scancode_discovery.host', '0.0.0.0');

        return is_string($host) && $host !== '' ? $host : '0.0.0.0';
    }

    public function port(): int
    {
        return (int) config('nativephp.scancode_discovery.port', 34254);
    }

    public function shouldRespond(string $payload): bool
    {
        return $payload === self::RequestPayload;
    }

    public function responsePayload(): ?string
    {
        $url = $this->configuredUrl() ?? $this->localNetworkService->baseUrl();

        if ($url === null) {
            return null;
        }

        return json_encode([
            'service' => $this->serviceName(),
            'url' => $url,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    private function configuredUrl(): ?string
    {
        $url = config('nativephp.scancode_discovery.url');

        return is_string($url) && $url !== '' ? $url : null;
    }

    private function serviceName(): string
    {
        $service = config('nativephp.scancode_discovery.service', 'scancode-desktop');

        return is_string($service) && $service !== '' ? $service : 'scancode-desktop';
    }
}
