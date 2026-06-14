<?php

namespace App\Console\Commands;

use App\Services\ScancodeDiscoveryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('app:scancode-discovery-listen {--once : Stop after receiving one UDP packet}')]
#[Description('Listen for Android scancode discovery UDP broadcasts')]
class ScancodeDiscoveryListenCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ScancodeDiscoveryService $discovery): int
    {
        $socket = $this->createSocket($discovery);

        if ($socket === false) {
            return self::FAILURE;
        }

        $this->info("Listening for scancode discovery on UDP {$discovery->host()}:{$discovery->port()}");
        Log::info('Scancode discovery UDP listener started.', [
            'host' => $discovery->host(),
            'port' => $discovery->port(),
            'pid' => getmypid(),
        ]);

        try {
            do {
                $peer = '';
                $payload = stream_socket_recvfrom($socket, 65535, 0, $peer);

                if (! is_string($payload)) {
                    continue;
                }

                if ($discovery->shouldRespond($payload)) {
                    $this->respond($socket, $peer, $discovery);
                } else {
                    Log::debug('Scancode discovery UDP packet ignored: unexpected payload.', [
                        'peer' => $peer,
                        'payload' => $payload,
                    ]);
                }
            } while (! $this->option('once'));
        } finally {
            fclose($socket);
            Log::info('Scancode discovery UDP listener stopped.', [
                'host' => $discovery->host(),
                'port' => $discovery->port(),
                'pid' => getmypid(),
            ]);
        }

        return self::SUCCESS;
    }

    /**
     * @return resource|false
     */
    private function createSocket(ScancodeDiscoveryService $discovery)
    {
        $socket = @stream_socket_server(
            "udp://{$discovery->host()}:{$discovery->port()}",
            $errorCode,
            $errorMessage,
            STREAM_SERVER_BIND,
        );

        if ($socket === false) {
            $this->error("Unable to bind UDP discovery socket: [{$errorCode}] {$errorMessage}");
            Log::error('Unable to bind scancode discovery UDP socket.', [
                'host' => $discovery->host(),
                'port' => $discovery->port(),
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'pid' => getmypid(),
            ]);
        }

        return $socket;
    }

    /**
     * @param  resource  $socket
     */
    private function respond($socket, string $peer, ScancodeDiscoveryService $discovery): void
    {
        $response = $discovery->responsePayload();

        if ($response === null) {
            $this->warn('Discovery request received, but the local network URL is unavailable.');
            Log::warning('Scancode discovery request received, but response URL is unavailable.', [
                'peer' => $peer,
            ]);

            return;
        }

        try {
            $bytes = stream_socket_sendto($socket, $response, 0, $peer);

            Log::info('Scancode discovery response sent.', [
                'peer' => $peer,
                'bytes' => $bytes,
                'response' => $response,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $this->error("Unable to send discovery response to {$peer}: {$exception->getMessage()}");
            Log::error('Unable to send scancode discovery response.', [
                'peer' => $peer,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
