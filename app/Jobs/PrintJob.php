<?php

namespace App\Jobs;

use App\Enums\PrintMethodEnum;
use App\Models\PrintSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PrintJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $path,
        public ?int $copies = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = $this->absolutePath();
        $setting = PrintSetting::current();
        $copies = max(1, $this->copies ?? $setting->copies);

        match ($setting->method) {
            PrintMethodEnum::Electron => $this->printWithElectron($path, $copies),
            PrintMethodEnum::NativeWindows => $this->printWithNativeWindows($path, $copies),
            PrintMethodEnum::SystemCommand => $this->printWithSystemCommand($path, $copies),
        };
    }

    private function printWithElectron(string $path, int $copies): void
    {
        $this->postToNativePhp('system/print-file', $path, $copies);
    }

    private function printWithNativeWindows(string $path, int $copies): void
    {
        $this->postToNativePhp('system/print-file-native-windows', $path, $copies);
    }

    private function printWithSystemCommand(string $path, int $copies): void
    {
        for ($copy = 0; $copy < $copies; $copy++) {
            match (PHP_OS_FAMILY) {
                'Windows' => $this->printOnWindows($path),
                'Linux', 'Darwin', 'BSD' => $this->printWithLp($path),
                default => throw new RuntimeException('Printing is not supported on '.PHP_OS_FAMILY.'.'),
            };
        }
    }

    private function printWithLp(string $path): void
    {
        Process::run(['lp', $path])->throw();
    }

    private function printOnWindows(string $path): void
    {
        Process::run([
            'powershell',
            '-NoProfile',
            '-ExecutionPolicy',
            'Bypass',
            '-Command',
            'Start-Process -FilePath '.$this->powerShellString($path).' -Verb Print -WindowStyle Hidden -ErrorAction Stop',
        ])->throw();
    }

    private function postToNativePhp(string $endpoint, string $path, int $copies): void
    {
        $apiUrl = getenv('NATIVEPHP_API_URL') ?: null;
        $secret = getenv('NATIVEPHP_SECRET') ?: null;

        if (! is_string($apiUrl) || $apiUrl === '' || ! is_string($secret) || $secret === '') {
            throw new RuntimeException('NativePHP internal API is not available.');
        }

        Http::withHeaders([
            'X-NativePHP-Secret' => $secret,
        ])
            ->acceptJson()
            ->timeout(120)
            ->post(rtrim($apiUrl, '/').'/'.$endpoint, [
                'filePath' => $path,
                'copies' => $copies,
            ])
            ->throw();
    }

    private function absolutePath(): string
    {
        if ($this->isAbsolutePath($this->path)) {
            return $this->path;
        }

        return Storage::disk('local')->path($this->path);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\\\')
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }

    private function powerShellString(string $value): string
    {
        return "'".str_replace("'", "''", $value)."'";
    }
}
