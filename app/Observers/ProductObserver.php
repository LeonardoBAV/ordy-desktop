<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     */
    public function creating(Product $product): void
    {
        $this->syncUnlimitedWithStockLimit($product);
    }

    /**
     * Handle the Product "updating" event.
     */
    public function updating(Product $product): void
    {
        $this->syncUnlimitedWithStockLimit($product);
    }

    private function syncUnlimitedWithStockLimit(Product $product): void
    {
        if ($product->stock_limit > 0) {
            $product->unlimited = false;
        }
    }
}
