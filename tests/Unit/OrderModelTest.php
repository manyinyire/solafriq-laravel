<?php

use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('order can be created with required fields', function () {
    $user = User::factory()->create();
    
    $order = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    expect($order->total_amount)->toBe('1500.00')
        ->and($order->status)->toBe('PENDING')
        ->and($order->user_id)->toBe($user->id);
});

test('order belongs to user', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    expect($order->user)->toBeInstanceOf(User::class)
        ->and($order->user->id)->toBe($user->id);
});

test('order has items relationship', function () {
    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    expect($order->items())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('order can check if paid', function () {
    $user = User::factory()->create();
    
    $paidOrder = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PAID',
    ]);

    $unpaidOrder = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    expect($paidOrder->isPaid())->toBeTrue()
        ->and($unpaidOrder->isPaid())->toBeFalse();
});

test('order can check if pending', function () {
    $user = User::factory()->create();
    
    $pendingOrder = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    $processingOrder = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PROCESSING',
        'payment_status' => 'PENDING',
    ]);

    expect($pendingOrder->isPending())->toBeTrue()
        ->and($processingOrder->isPending())->toBeFalse();
});

test('order can check if completed', function () {
    $user = User::factory()->create();
    
    $completedOrder = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'DELIVERED',
        'payment_status' => 'PAID',
    ]);

    $pendingOrder = Order::create([
        'user_id' => $user->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    expect($completedOrder->isCompleted())->toBeTrue()
        ->and($pendingOrder->isCompleted())->toBeFalse();
});

test('order scope filters by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    Order::create([
        'user_id' => $user1->id,
        'total_amount' => 1500.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    Order::create([
        'user_id' => $user2->id,
        'total_amount' => 2000.00,
        'status' => 'PENDING',
        'payment_status' => 'PENDING',
    ]);

    $user1Orders = Order::forUser($user1->id)->get();

    expect($user1Orders)->toHaveCount(1)
        ->and($user1Orders->first()->user_id)->toBe($user1->id);
});

test('order scope filters by status', function () {
    $user = User::factory()->create();
    
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

    $pendingOrders = Order::withStatus('PENDING')->get();

    expect($pendingOrders)->toHaveCount(1)
        ->and($pendingOrders->first()->status)->toBe('PENDING');
});
