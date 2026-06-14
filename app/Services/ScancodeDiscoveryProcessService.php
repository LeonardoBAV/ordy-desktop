<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Native\Desktop\Facades\ChildProcess;
use RuntimeException;

class ScancodeDiscoveryProcessService
{
    private const PROCESS_ALIAS = 'scancode_discovery';

    private const STARTING_CACHE_KEY = 'scancode_discovery_starting';

    private const STARTING_CACHE_SECONDS = 15;

    private static bool $ensureAttemptedThisRequest = false;

    public function __construct(
        private NativePhpApiService $nativePhpApi,
    ) {}

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
        if (! $this->nativePhpApi->isAvailable()) {
            Log::debug('Scancode discovery status unavailable: NativePHP internal API is not available.');

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
                Log::warning('Unable to read scancode discovery process status.', [
                    'alias' => self::PROCESS_ALIAS,
                    'status' => $exception->response?->status(),
                    'message' => $exception->getMessage(),
                ]);

                throw $exception;
            }

            Log::debug('Scancode discovery process is not registered in NativePHP.', [
                'alias' => self::PROCESS_ALIAS,
            ]);

            $data = [];
        }

        $pid = $data['pid'] ?? null;
        $running = is_numeric($pid);

        if ($running) {
            Cache::forget(self::STARTING_CACHE_KEY);
        }

        return [
            'available' => true,
            'running' => $running,
            'pid' => $running ? (int) $pid : null,
            'alias' => self::PROCESS_ALIAS,
        ];
    }

    public function ensureRunning(): void
    {
        if (self::$ensureAttemptedThisRequest) {
            return;
        }

        self::$ensureAttemptedThisRequest = true;

        if (! config('nativephp.scancode_discovery.enabled', true)) {
            Log::info('Scancode discovery process start skipped: discovery is disabled.');

            return;
        }

        if (! $this->nativePhpApi->isAvailable()) {
            Log::warning('Scancode discovery process start skipped: NativePHP internal API is unavailable.');

            return;
        }

        if ($this->status()['running']) {
            Log::debug('Scancode discovery process start skipped: already running.', [
                'alias' => self::PROCESS_ALIAS,
            ]);

            return;
        }

        if (Cache::has(self::STARTING_CACHE_KEY)) {
            Log::debug('Scancode discovery process start skipped: spawn already in progress.', [
                'alias' => self::PROCESS_ALIAS,
            ]);

            return;
        }

        try {
            Cache::lock(self::PROCESS_ALIAS.':start', 10)->block(3, function (): void {
                if ($this->status()['running']) {
                    return;
                }

                if (Cache::has(self::STARTING_CACHE_KEY)) {
                    return;
                }

                Cache::put(self::STARTING_CACHE_KEY, true, self::STARTING_CACHE_SECONDS);

                Log::info('Starting scancode discovery process.', [
                    'alias' => self::PROCESS_ALIAS,
                ]);

                ChildProcess::artisan(
                    'app:scancode-discovery-listen',
                    self::PROCESS_ALIAS,
                    env: [
                        'NATIVEPHP_RUNNING' => 'true',
                    ],
                    persistent: true,
                );
            });
        } catch (LockTimeoutException $exception) {
            Log::debug('Scancode discovery process start skipped: waiting for another start attempt.', [
                'alias' => self::PROCESS_ALIAS,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function restart(): void
    {
        if (! config('nativephp.scancode_discovery.enabled', true)) {
            Log::info('Scancode discovery process restart skipped: discovery is disabled.');

            return;
        }

        Cache::forget(self::STARTING_CACHE_KEY);
        self::$ensureAttemptedThisRequest = false;

        $status = $this->status();

        if (! $status['available']) {
            Log::warning('Scancode discovery process restart skipped: NativePHP internal API is unavailable.');

            return;
        }

        if (! $status['running']) {
            Log::info('Scancode discovery process is stopped; starting instead of restarting.', [
                'alias' => self::PROCESS_ALIAS,
            ]);

            $this->ensureRunning();

            return;
        }

        try {
            Log::info('Restarting scancode discovery process.', [
                'alias' => self::PROCESS_ALIAS,
                'pid' => $status['pid'],
            ]);

            $response = $this->nativePhpApi->post('child-process/restart', [
                'alias' => self::PROCESS_ALIAS,
            ]);

            Log::info('Scancode discovery process restart requested.', [
                'alias' => self::PROCESS_ALIAS,
                'pid' => $response['pid'] ?? null,
                'error' => $response['error'] ?? null,
            ]);

            $this->throwIfProcessApiReturnedError($response);
        } catch (RequestException $exception) {
            if ($exception->response?->status() !== 410) {
                Log::warning('Unable to restart scancode discovery process.', [
                    'alias' => self::PROCESS_ALIAS,
                    'status' => $exception->response?->status(),
                    'message' => $exception->getMessage(),
                ]);

                throw $exception;
            }

            Log::info('Scancode discovery process disappeared before restart; starting it again.', [
                'alias' => self::PROCESS_ALIAS,
            ]);

            $this->ensureRunning();
        }
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function throwIfProcessApiReturnedError(array $response): void
    {
        $error = $response['error'] ?? null;

        if (! is_string($error) || $error === '') {
            return;
        }

        throw new RuntimeException($error);
    }
}
