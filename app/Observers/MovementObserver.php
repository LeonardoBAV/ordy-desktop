<?php

namespace App\Observers;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class MovementObserver
{
    /**
     * Handle the Movement "creating" event.
     */
    public function creating(Movement $movement): void
    {
        $this->ensureProductHasAvailableStock($movement);
    }

    /**
     * Handle the Movement "updating" event.
     */
    public function updating(Movement $movement): void
    {
        $this->ensureProductHasAvailableStock($movement);
    }

    private function ensureProductHasAvailableStock(Movement $movement): void
    {
        $product = Product::findById($movement->product_id);

        $totalQty = $product->totalQuantityUsed(except: $movement);

        if (($totalQty + $movement->qty) > $product->stock_limit && !$product->unlimited) {
            throw ValidationException::withMessages([
                'qty' => __('inventory.products.unavailable_insufficient_units'),
            ]);
        }

    }
}
