<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([ProductObserver::class])]
#[Fillable(['sku', 'name', 'stock_limit', 'unlimited'])]
class Product extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'stock_limit' => 0,
        'unlimited' => true,
    ];

    /**
     * @return HasMany<Movement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeWithUsedQuantity(Builder $query): Builder
    {
        return $query->withSum('movements as used_quantity', 'qty');
    }

    public static function findById(int $id): ?self
    {
        return self::find($id, ['*']);
    }

    public function totalQuantityUsed(?Movement $except = null): int
    {
        return (int) $this->movements()
            ->when($except?->exists, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->sum('qty');
    }

    public function usedQuantity(): int
    {
        if (array_key_exists('used_quantity', $this->attributes)) {
            return (int) ($this->attributes['used_quantity'] ?? 0);
        }

        return $this->totalQuantityUsed();
    }

    public function availableQuantity(): ?int
    {
        if ($this->unlimited) {
            return null;
        }

        return max($this->stock_limit - $this->usedQuantity(), 0);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock_limit' => 'integer',
            'unlimited' => 'boolean',
        ];
    }
}
