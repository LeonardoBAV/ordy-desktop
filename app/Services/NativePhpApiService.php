<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NativePhpApiService
{
    public function isAvailable(): bool
    {
        $apiUrl = getenv('NATIVEPHP_API_URL') ?: null;
        $secret = getenv('NATIVEPHP_SECRET') ?: null;

        return is_string($apiUrl) && $apiUrl !== '' && is_string($secret) && $secret !== '';
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $endpoint): array
    {
        return $this->request('get', $endpoint)->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('post', $endpoint, $data)->json() ?? [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function request(string $method, string $endpoint, array $data = []): Response
    {
        if (! $this->isAvailable()) {
            throw new RuntimeException('NativePHP internal API is not available.');
        }

        $apiUrl = getenv('NATIVEPHP_API_URL');
        $secret = getenv('NATIVEPHP_SECRET');

        $request = Http::withHeaders([
            'X-NativePHP-Secret' => $secret,
        ])
            ->acceptJson()
            ->timeout(10);

        $url = rtrim($apiUrl, '/').'/'.ltrim($endpoint, '/');

        $response = $method === 'get'
            ? $request->get($url)
            : $request->post($url, $data);

        $response->throw();

        return $response;
    }
}
