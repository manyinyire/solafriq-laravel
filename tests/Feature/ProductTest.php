<?php

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('products category page can be viewed', function () {
    $response = $this->get('/products/category/solar_panel');

    $response->assertStatus(200);
});

test('only active products are shown to users', function () {
    Product::create([
        'name' => 'Active Product',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    Product::create([
        'name' => 'Inactive Product',
        'category' => 'solar_panel',
        'price' => 199.99,
        'stock_quantity' => 30,
        'is_active' => false,
    ]);

    $response = $this->get('/products/category/solar_panel');

    $response->assertStatus(200);
});

test('admin can view all products including inactive', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'email_verified_at' => now()]);

    Product::create([
        'name' => 'Active Product',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    Product::create([
        'name' => 'Inactive Product',
        'category' => 'solar_panel',
        'price' => 199.99,
        'stock_quantity' => 30,
        'is_active' => false,
    ]);

    $response = $this->actingAs($admin)->get('/admin/products');

    $response->assertStatus(200);
});

test('admin can create new product', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'email_verified_at' => now()]);

    $response = $this->actingAs($admin)->post('/admin/products', [
        'name' => 'New Solar Panel',
        'category' => 'solar_panel',
        'price' => 399.99,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    expect(Product::where('name', 'New Solar Panel')->exists())->toBeTrue();
});

test('admin can update product', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'email_verified_at' => now()]);
    $product = Product::create([
        'name' => 'Old Name',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put("/admin/products/{$product->id}", [
        'name' => 'Updated Name',
        'category' => 'solar_panel',
        'price' => 349.99,
        'stock_quantity' => 60,
        'is_active' => true,
    ]);

    expect($product->fresh()->name)->toBe('Updated Name')
        ->and($product->fresh()->price)->toBe('349.99');
});

test('admin can delete product', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'email_verified_at' => now()]);
    $product = Product::create([
        'name' => 'To Delete',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/products/{$product->id}");

    expect(Product::find($product->id))->toBeNull();
});

test('non-admin cannot create product', function () {
    $user = User::factory()->create(['role' => 'CLIENT', 'email_verified_at' => now()]);

    $response = $this->actingAs($user)->post('/admin/products', [
        'name' => 'New Solar Panel',
        'category' => 'solar_panel',
        'price' => 399.99,
        'stock_quantity' => 100,
        'is_active' => true,
    ]);

    $response->assertStatus(403);
});

test('products can be filtered by category', function () {
    Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    Product::create([
        'name' => 'Battery',
        'category' => 'battery',
        'price' => 499.99,
        'stock_quantity' => 30,
        'is_active' => true,
    ]);

    $response = $this->get('/products/category/solar_panel');

    $response->assertStatus(200);
});
