<?php

namespace App\Filament\Widgets;

use App\Services\LocalNetworkService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Filament\Widgets\Widget;

class LocalNetworkQrCodeWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected string $view = 'filament.widgets.local-network-qr-code-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $baseUrl = app(LocalNetworkService::class)->baseUrl();

        return [
            'baseUrl' => $baseUrl,
            'qrCodeDataUri' => $this->qrCodeDataUri($baseUrl),
        ];
    }

    private function qrCodeDataUri(?string $baseUrl): ?string
    {
        if ($baseUrl === null) {
            return null;
        }

        return (new QRCode(new QROptions([
            'outputBase64' => true,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'quietzoneSize' => 2,
        ])))->render($baseUrl);
    }
}
