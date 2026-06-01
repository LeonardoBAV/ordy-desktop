<?php

use App\Models\Movement;
use App\Models\Product;
use Database\Seeders\MovementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('it blocks creating a movement above the product stock limit', function () {
    app()->setLocale('pt_BR');

    $product = Product::create([
        'sku' => 'SKU-001',
        'name' => 'Produto limitado',
        'stock_limit' => 10,
    ]);

    Movement::create([
        'product_id' => $product->id,
        'qty' => 6,
    ]);

    expect(fn () => Movement::create([
        'product_id' => $product->id,
        'qty' => 5,
    ]))->toThrow(ValidationException::class, __('inventory.products.unavailable_insufficient_units'));
});

test('it blocks updating a movement above the product stock limit', function () {
    app()->setLocale('pt_BR');

    $product = Product::create([
        'sku' => 'SKU-002',
        'name' => 'Produto limitado',
        'stock_limit' => 10,
    ]);

    Movement::create([
        'product_id' => $product->id,
        'qty' => 6,
    ]);

    $movement = Movement::create([
        'product_id' => $product->id,
        'qty' => 3,
    ]);

    expect(fn () => $movement->update(['qty' => 5]))
        ->toThrow(ValidationException::class, __('inventory.products.unavailable_insufficient_units'));
});

test('it allows movements for unlimited products', function () {
    $product = Product::create([
        'sku' => 'SKU-003',
        'name' => 'Produto ilimitado',
        'stock_limit' => 0,
    ]);

    $movement = Movement::create([
        'product_id' => $product->id,
        'qty' => 100,
    ]);

    expect($movement->exists)->toBeTrue();
});

test('movement seeder respects product stock limits', function () {
    foreach (range(1, 6) as $index) {
        Product::create([
            'sku' => "SKU-SEED-{$index}",
            'name' => "Produto {$index}",
            'stock_limit' => 10,
        ]);
    }

    Artisan::call('db:seed', [
        '--class' => MovementSeeder::class,
    ]);

    Product::all()
        ->each(function (Product $product): void {
            expect($product->totalQuantityUsed())->toBeLessThanOrEqual($product->stock_limit);
        });
});
