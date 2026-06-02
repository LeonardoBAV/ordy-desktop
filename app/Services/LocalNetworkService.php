<?php

namespace App\Services;

class LocalNetworkService
{
    private bool $localIpResolved = false;

    private ?string $localIp = null;

    public function localIp(): ?string
    {
        if ($this->localIpResolved) {
            return $this->localIp;
        }

        $this->localIpResolved = true;

        foreach ($this->networkCommandOutputs() as $output) {
            foreach ($this->privateIpv4Candidates($output) as $ip) {
                return $this->localIp = $ip;
            }
        }

        $hostnameIp = gethostbyname(gethostname() ?: '');

        if ($this->isPrivateIpv4($hostnameIp)) {
            return $this->localIp = $hostnameIp;
        }

        return null;
    }

    public function port(): int
    {
        return (int) (config('nativephp.php_server.port')
            ?: parse_url(config('app.url'), PHP_URL_PORT)
            ?: 8000);
    }

    public function baseUrl(): ?string
    {
        $ip = $this->localIp();

        if ($ip === null) {
            return null;
        }

        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';

        return "{$scheme}://{$ip}:{$this->port()}";
    }

    /**
     * @return array<int, string>
     */
    protected function networkCommandOutputs(): array
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => [
                $this->runCommand('powershell -NoProfile -Command "(Get-NetIPConfiguration | Where-Object { $_.IPv4DefaultGateway -ne $null -and $_.NetAdapter.Status -eq \'Up\' } | Select-Object -First 1 -ExpandProperty IPv4Address).IPAddress"'),
                $this->runCommand('ipconfig'),
            ],
            default => [
                $this->runCommand('ip route get 1.1.1.1 2>/dev/null'),
                $this->runCommand('hostname -I 2>/dev/null'),
                $this->runCommand('ifconfig 2>/dev/null'),
            ],
        };
    }

    protected function runCommand(string $command): string
    {
        $lines = [];
        $exitCode = 1;

        exec($command, $lines, $exitCode);

        if ($exitCode !== 0) {
            return '';
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return array<int, string>
     */
    protected function privateIpv4Candidates(string $output): array
    {
        $candidates = [];

        if (preg_match('/\bsrc\s+((?:\d{1,3}\.){3}\d{1,3})\b/', $output, $srcMatch) === 1) {
            $candidates[] = $srcMatch[1];
        }

        preg_match_all('/\b(?:\d{1,3}\.){3}\d{1,3}\b/', $output, $matches);
        $candidates = array_merge($candidates, $matches[0]);

        return array_values(array_filter(
            array_unique($candidates),
            fn (string $ip): bool => $this->isPrivateIpv4($ip),
        ));
    }

    protected function isPrivateIpv4(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $ipLong = ip2long($ip);

        if ($ipLong === false) {
            return false;
        }

        return $this->isInRange($ipLong, '10.0.0.0', '10.255.255.255')
            || $this->isInRange($ipLong, '172.16.0.0', '172.31.255.255')
            || $this->isInRange($ipLong, '192.168.0.0', '192.168.255.255');
    }

    protected function isInRange(int $ipLong, string $from, string $to): bool
    {
        return $ipLong >= ip2long($from) && $ipLong <= ip2long($to);
    }
}
