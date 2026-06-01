<?php

namespace Database\Seeders;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Product::all() as $product) {
            $movementCount = random_int(0, 3);

            if ($movementCount === 0) {
                continue;
            }

            if ($product->unlimited) {
                $this->createUnlimitedProductMovements($product, $movementCount);

                continue;
            }

            $availableQty = max(0, $product->stock_limit - $product->totalQuantityUsed());

            if ($availableQty === 0) {
                continue;
            }

            $totalQty = random_int(0, $availableQty);

            if ($totalQty === 0) {
                continue;
            }

            foreach ($this->splitQuantity($totalQty, min($movementCount, $totalQty)) as $qty) {
                Movement::createForProduct($product, $qty);
            }
        }
    }

    private function createUnlimitedProductMovements(Product $product, int $movementCount): void
    {
        for ($i = 0; $i < $movementCount; $i++) {
            Movement::createForProduct($product, random_int(1, 20));
        }
    }

    /**
     * @return array<int, int>
     */
    private function splitQuantity(int $totalQty, int $parts): array
    {
        $quantities = [];
        $remainingQty = $totalQty;

        for ($i = 1; $i <= $parts; $i++) {
            $remainingParts = $parts - $i;
            $maxQty = $remainingQty - $remainingParts;
            $qty = $i === $parts ? $remainingQty : random_int(1, $maxQty);

            $quantities[] = $qty;
            $remainingQty -= $qty;
        }

        return $quantities;
    }
}
