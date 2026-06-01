<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('it creates a movement through the api', function () {
    $product = Product::create([
        'sku' => 'SKU-API-001',
        'name' => 'Produto API',
        'stock_limit' => 10,
    ]);

    $movementUuid = (string) Str::uuid();

    $response = postJson('/api/movements', [
        'sku' => $product->sku,
        'movement_uuid' => $movementUuid,
        'qty' => 3,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.product_id', $product->id)
        ->assertJsonPath('data.movement_uuid', $movementUuid)
        ->assertJsonPath('data.qty', 3);

    assertDatabaseHas('movements', [
        'product_id' => $product->id,
        'movement_uuid' => $movementUuid,
        'qty' => 3,
    ]);
});

test('it requires all movement fields', function () {
    $response = postJson('/api/movements', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'sku',
            'movement_uuid',
            'qty',
        ]);
});

test('it updates an existing movement when the uuid already exists', function () {
    $product = Product::create([
        'sku' => 'SKU-API-002',
        'name' => 'Produto API update',
        'stock_limit' => 10,
    ]);

    $movementUuid = (string) Str::uuid();

    postJson('/api/movements', [
        'sku' => $product->sku,
        'movement_uuid' => $movementUuid,
        'qty' => 3,
    ])->assertCreated();

    $response = postJson('/api/movements', [
        'sku' => $product->sku,
        'movement_uuid' => $movementUuid,
        'qty' => 7,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.movement_uuid', $movementUuid)
        ->assertJsonPath('data.qty', 7);

    assertDatabaseHas('movements', [
        'product_id' => $product->id,
        'movement_uuid' => $movementUuid,
        'qty' => 7,
    ]);
});

test('it deletes an existing movement when quantity is zero', function () {
    $product = Product::create([
        'sku' => 'SKU-API-003',
        'name' => 'Produto API delete',
        'stock_limit' => 10,
    ]);

    $movementUuid = (string) Str::uuid();

    postJson('/api/movements', [
        'sku' => $product->sku,
        'movement_uuid' => $movementUuid,
        'qty' => 3,
    ])->assertCreated();

    $response = postJson('/api/movements', [
        'sku' => $product->sku,
        'movement_uuid' => $movementUuid,
        'qty' => 0,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.movement_uuid', $movementUuid);

    assertDatabaseMissing('movements', [
        'movement_uuid' => $movementUuid,
    ]);
});
