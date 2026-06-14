<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Guarded(['*'])]
class FailedJob extends Model
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    /**
     * @return array<string, mixed>
     */
    public function decodedPayload(): array
    {
        $payload = json_decode($this->payload, true);

        return is_array($payload) ? $payload : [];
    }

    public function displayName(): string
    {
        return (string) ($this->decodedPayload()['displayName'] ?? $this->decodedPayload()['job'] ?? $this->uuid);
    }

    public function exceptionSummary(): string
    {
        return Str::limit(strtok($this->exception, "\n") ?: $this->exception, 160);
    }
}
