<?php

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view their orders', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    
    Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    $response = $this->actingAs($user)->get('/orders');

    $response->assertStatus(200);
});

test('user can view specific order details', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    
    $order = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    $response = $this->actingAs($user)->get("/orders/{$order->id}");

    $response->assertStatus(200);
});

test('user cannot view another users order', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);
    
    $order = Order::create([
        'user_id' => $user2->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    $response = $this->actingAs($user1)->get("/orders/{$order->id}");

    $response->assertStatus(403);
});

test('admin can view all orders', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'email_verified_at' => now()]);
    $user = User::factory()->create(['role' => 'CLIENT', 'email_verified_at' => now()]);
    
    Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    $response = $this->actingAs($admin)->get('/admin/orders');

    $response->assertStatus(200);
});

test('admin can update order status', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'email_verified_at' => now()]);
    $user = User::factory()->create(['role' => 'CLIENT', 'email_verified_at' => now()]);
    
    $order = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    $response = $this->actingAs($admin)->put("/admin/orders/{$order->id}/status", [
        'status' => 'PROCESSING',
    ]);

    expect($order->fresh()->status)->toBe('PROCESSING');
});

test('order calculates subtotal correctly', function () {
    $user = User::factory()->create();
    $product1 = Product::create([
        'name' => 'Product 1',
        'category' => 'solar_panel',
        'price' => 100.00,
        'stock_quantity' => 50,
        'is_active' => true,
    ]);
    
    $product2 = Product::create([
        'name' => 'Product 2',
        'category' => 'battery',
        'price' => 200.00,
        'stock_quantity' => 30,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'total_amount' => 0,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product1->id,
        'item_type' => 'product',
        'quantity' => 2,
        'price' => 100.00,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product2->id,
        'item_type' => 'product',
        'quantity' => 1,
        'price' => 200.00,
    ]);

    expect($order->fresh()->subtotal)->toBe(400.0);
});

test('user can filter orders by status', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    
    Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    Order::create([
        'user_id' => $user->id,
        'total_amount' => 2000.00,
        'status' => 'DELIVERED',
        'payment_status' => 'PAID',
    ]);

    $response = $this->actingAs($user)->get('/orders?status=PENDING');

    $response->assertStatus(200);
});

test('guest cannot view orders', function () {
    $response = $this->get('/orders');

    $response->assertRedirect('/login');
});
