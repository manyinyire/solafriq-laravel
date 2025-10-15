<?php

use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\SolarSystem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can view cart page', function () {
    $response = $this->get(route('cart.index'));

    $response->assertStatus(200);
});

test('authenticated user can view cart page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('cart.index'));

    $response->assertStatus(200);
});

test('user can add product to cart', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('cart.add'), [
        'type' => 'product',
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response->assertRedirect();
    
    expect(CartItem::where('product_id', $product->id)->count())->toBe(1)
        ->and(CartItem::where('product_id', $product->id)->first()->quantity)->toBe(2);
});

test('user can add solar system to cart', function () {
    $user = User::factory()->create();
    $solarSystem = SolarSystem::create([
        'name' => 'Basic Solar System',
        'description' => 'A basic solar system',
        'price' => 5000.00,
        'capacity' => 5.0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('cart.add'), [
        'type' => 'solar_system',
        'system_id' => $solarSystem->id,
        'quantity' => 1,
    ]);

    $response->assertRedirect();
    
    expect(CartItem::where('solar_system_id', $solarSystem->id)->count())->toBe(1);
});

test('cannot add inactive product to cart', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Inactive Product',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => false,
    ]);

    $response = $this->actingAs($user)->post(route('cart.add'), [
        'type' => 'product',
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response->assertSessionHasErrors();
});

test('cannot add more products than stock quantity', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Limited Stock',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('cart.add'), [
        'type' => 'product',
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $response->assertSessionHasErrors();
});

test('user can update cart item quantity', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);
    
    $cartItem = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'item_type' => 'product',
        'quantity' => 1,
        'price' => $product->price,
    ]);

    $response = $this->actingAs($user)->put(route('cart.update', $cartItem), [
        'quantity' => 3,
    ]);

    $response->assertRedirect();
    
    expect($cartItem->fresh()->quantity)->toBe(3);
});

test('user can remove item from cart', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);
    
    $cartItem = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'item_type' => 'product',
        'quantity' => 1,
        'price' => $product->price,
    ]);

    $response = $this->actingAs($user)->delete(route('cart.remove', $cartItem));

    $response->assertRedirect();
    
    expect(CartItem::find($cartItem->id))->toBeNull();
});

test('user can clear entire cart', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);
    
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'item_type' => 'product',
        'quantity' => 1,
        'price' => $product->price,
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'item_type' => 'product',
        'quantity' => 2,
        'price' => $product->price,
    ]);

    $response = $this->actingAs($user)->delete(route('cart.clear'));

    $response->assertRedirect();
    
    expect($cart->items()->count())->toBe(0);
});

test('user cannot update another users cart item', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $cart = Cart::create(['user_id' => $user2->id]);
    $product = Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);
    
    $cartItem = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'item_type' => 'product',
        'quantity' => 1,
        'price' => $product->price,
    ]);

    $response = $this->actingAs($user1)->put(route('cart.update', $cartItem), [
        'quantity' => 5,
    ]);

    $response->assertSessionHasErrors();
});

test('adding same product increases quantity', function () {
    $user = User::factory()->create();
    $product = Product::create([
        'name' => 'Solar Panel',
        'category' => 'solar_panel',
        'price' => 299.99,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('cart.add'), [
        'type' => 'product',
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)->post(route('cart.add'), [
        'type' => 'product',
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    expect(CartItem::where('product_id', $product->id)->count())->toBe(1)
        ->and(CartItem::where('product_id', $product->id)->first()->quantity)->toBe(5);
});
