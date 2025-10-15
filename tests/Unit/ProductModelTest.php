<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('product can be created with required fields', function () {
    $product = Product::create([
        'name' => 'Solar Panel 300W',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
    ]);

    expect($product->name)->toBe('Solar Panel 300W')
        ->and($product->category)->toBe('solar_panel')
        ->and($product->price)->toBe('299.99')
        ->and($product->stock_quantity)->toBe(50);
});

test('product specifications are cast to array', function () {
    $product = Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'specifications' => ['wattage' => '300W', 'efficiency' => '20%'],
    ]);

    expect($product->specifications)->toBeArray()
        ->and($product->specifications['wattage'])->toBe('300W');
});

test('product active scope returns only active products', function () {
    Product::create([
        'name' => 'Active Product',
        'category' => 'solar_panel',
        'price' => 100,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    Product::create([
        'name' => 'Inactive Product',
        'category' => 'solar_panel',
        'price' => 100,
        'stock_quantity' => 10,
        'is_active' => false,
    ]);

    $activeProducts = Product::active()->get();

    expect($activeProducts)->toHaveCount(1)
        ->and($activeProducts->first()->name)->toBe('Active Product');
});

test('product category scope filters by category', function () {
    Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 100,
        'stock_quantity' => 10,
    ]);

    Product::create([
        'name' => 'Battery',
        'category' => 'battery',
        'price' => 200,
        'stock_quantity' => 5,
    ]);

    $solarPanels = Product::category('solar_panel')->get();

    expect($solarPanels)->toHaveCount(1)
        ->and($solarPanels->first()->name)->toBe('Solar Panel');
});

test('product full name attribute combines brand model and name', function () {
    $product = Product::create([
        'name' => 'Monocrystalline',
        'brand' => 'SunPower',
        'model' => 'X-Series',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
    ]);

    expect($product->full_name)->toBe('SunPower X-Series Monocrystalline');
});

test('product price is cast to decimal', function () {
    $product = Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.999,
        'stock_quantity' => 50,
    ]);

    expect($product->price)->toBe('300.00');
});
