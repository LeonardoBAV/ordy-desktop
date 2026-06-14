<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Native\Desktop\Facades\QueueWorker;

class QueueWorkerService
{
    private const PROCESS_ALIAS = 'queue_default';

    public function __construct(
        private NativePhpApiService $nativePhpApi,
    ) {}

    public function isAvailable(): bool
    {
        return $this->nativePhpApi->isAvailable();
    }

    /**
     * @return array{
     *     available: bool,
     *     running: bool,
     *     pid: int|null,
     *     alias: string
     * }
     */
    public function status(): array
    {
        if (! $this->isAvailable()) {
            return [
                'available' => false,
                'running' => false,
                'pid' => null,
                'alias' => self::PROCESS_ALIAS,
            ];
        }

        try {
            $data = $this->nativePhpApi->get('child-process/get/'.self::PROCESS_ALIAS);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 410) {
                throw $exception;
            }

            $data = [];
        }

        $pid = $data['pid'] ?? null;

        return [
            'available' => true,
            'running' => is_numeric($pid),
            'pid' => is_numeric($pid) ? (int) $pid : null,
            'alias' => self::PROCESS_ALIAS,
        ];
    }

    public function restart(): void
    {
        try {
            $this->nativePhpApi->post('child-process/restart', [
                'alias' => self::PROCESS_ALIAS,
            ]);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 410) {
                throw $exception;
            }

            QueueWorker::up('default');
        }
    }
}
