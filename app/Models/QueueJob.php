<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['*'])]
class QueueJob extends Model
{
    protected $table = 'jobs';

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
        return (string) ($this->decodedPayload()['displayName'] ?? $this->decodedPayload()['job'] ?? $this->id);
    }
}
