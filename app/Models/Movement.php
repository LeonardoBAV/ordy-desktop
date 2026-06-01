<?php

namespace App\Models;

use App\Observers\MovementObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[ObservedBy([MovementObserver::class])]
#[Fillable(['product_id', 'movement_uuid', 'qty'])]
class Movement extends Model
{
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function createForProduct(Product $product, int $qty): self
    {
        return $product->movements()->create([
            'movement_uuid' => (string) Str::uuid(),
            'qty' => $qty,
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (Movement $movement): void {
            $movement->movement_uuid ??= (string) Str::uuid();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'qty' => 'integer',
        ];
    }
}
