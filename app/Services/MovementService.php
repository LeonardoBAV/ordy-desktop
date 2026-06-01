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
    public function create(array $data): Movement
    {
        $product = Product::findBySku($data['sku']);

        if (! $product instanceof Product) {
            throw ValidationException::withMessages([
                'sku' => __('validation.exists', ['attribute' => 'sku']),
            ]);
        }

        $movement = Movement::findByUuid($data['movement_uuid']);
        $quantity = (int) $data['qty'];

        if ($quantity === 0) {
            if (! $movement instanceof Movement) {
                throw ValidationException::withMessages([
                    'movement_uuid' => __('validation.exists', ['attribute' => 'movement_uuid']),
                ]);
            }

            $movement->deleteOrFail();

            return $movement;
        }

        $attributes = [
            'product_id' => $product->getKey(),
            'movement_uuid' => $data['movement_uuid'],
            'qty' => $quantity,
        ];

        if ($movement instanceof Movement) {
            $movement->update($attributes);

            return $movement;
        }

        return Movement::create($attributes);
    }
}
