<?php

use App\Services\LocalNetworkService;
use App\Services\ScancodeDiscoveryService;

test('it only responds to the exact discovery payload', function () {
    $service = app(ScancodeDiscoveryService::class);

    expect($service->shouldRespond('SCANCODE_DISCOVERY_REQUEST'))->toBeTrue()
        ->and($service->shouldRespond('SCANCODE_DISCOVERY_REQUEST'.PHP_EOL))->toBeFalse()
        ->and($service->shouldRespond(' SCANCODE_DISCOVERY_REQUEST'))->toBeFalse()
        ->and($service->shouldRespond('{"payload":"SCANCODE_DISCOVERY_REQUEST"}'))->toBeFalse();
});

test('it builds the discovery response JSON', function () {
    app()->instance(LocalNetworkService::class, new class extends LocalNetworkService
    {
        public function baseUrl(): ?string
        {
            return 'http://192.168.0.20:3333';
        }
    });

    config()->set('nativephp.scancode_discovery.service', 'scancode-desktop');
    config()->set('nativephp.scancode_discovery.url', null);

    expect(app(ScancodeDiscoveryService::class)->responsePayload())
        ->toBe('{"service":"scancode-desktop","url":"http://192.168.0.20:3333"}');
});

test('it can use an explicit discovery url override', function () {
    config()->set('nativephp.scancode_discovery.url', 'http://192.168.0.20:3333');

    expect(app(ScancodeDiscoveryService::class)->responsePayload())
        ->toBe('{"service":"scancode-desktop","url":"http://192.168.0.20:3333"}');
});
