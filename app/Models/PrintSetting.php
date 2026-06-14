<?php

namespace App\Models;

use App\Enums\PrintMethodEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['method', 'copies'])]
class PrintSetting extends Model
{
    protected $attributes = [
        'method' => PrintMethodEnum::Electron->value,
        'copies' => 1,
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => PrintMethodEnum::class,
            'copies' => 'integer',
        ];
    }
}
