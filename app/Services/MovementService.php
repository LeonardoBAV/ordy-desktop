<?php

namespace App\Services;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class MovementService
{
    /**
     * @param  array{sku: string, movement_uuid: string, qty: int}  $data
     */
    public function createFromSku(array $data): Movement
    {
        $product = Product::findBySku($data['sku']);

        if (! $product instanceof Product) {
            throw ValidationException::withMessages([
                'sku' => __('validation.exists', ['attribute' => 'sku']),
            ]);
        }

        return Movement::create([
            'product_id' => $product->getKey(),
            'movement_uuid' => $data['movement_uuid'],
            'qty' => $data['qty'],
        ]);
    }
}
