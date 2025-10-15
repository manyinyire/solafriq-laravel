<?php

use App\Models\Cart;
use App\Models\User;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cart can be created for authenticated user', function () {
    $user = User::factory()->create();
    
    $cart = Cart::create([
        'user_id' => $user->id,
        'session_id' => null,
    ]);

    expect($cart->user_id)->toBe($user->id)
        ->and($cart->session_id)->toBeNull();
});

test('cart can be created for guest with session', function () {
    $cart = Cart::create([
        'user_id' => null,
        'session_id' => 'test-session-id',
    ]);

    expect($cart->user_id)->toBeNull()
        ->and($cart->session_id)->toBe('test-session-id');
});

test('cart belongs to user', function () {
    $user = User::factory()->create();
    $cart = Cart::create([
        'user_id' => $user->id,
        'session_id' => null,
    ]);

    expect($cart->user)->toBeInstanceOf(User::class)
        ->and($cart->user->id)->toBe($user->id);
});

test('cart has items relationship', function () {
    $cart = Cart::create([
        'user_id' => null,
        'session_id' => 'test-session',
    ]);

    expect($cart->items())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('cart metadata is cast to array', function () {
    $cart = Cart::create([
        'user_id' => null,
        'session_id' => 'test-session',
        'metadata' => ['discount_code' => 'SAVE10'],
    ]);

    expect($cart->metadata)->toBeArray()
        ->and($cart->metadata['discount_code'])->toBe('SAVE10');
});

test('cart get for session creates new cart if not exists', function () {
    $cart = Cart::getForSession('new-session-123');

    expect($cart)->toBeInstanceOf(Cart::class)
        ->and($cart->session_id)->toBe('new-session-123')
        ->and($cart->user_id)->toBeNull();
});

test('cart get for session returns existing cart', function () {
    $existingCart = Cart::create([
        'user_id' => null,
        'session_id' => 'existing-session',
    ]);

    $cart = Cart::getForSession('existing-session');

    expect($cart->id)->toBe($existingCart->id);
});

test('cart get for session with user id creates user cart', function () {
    $user = User::factory()->create();
    
    $cart = Cart::getForSession('some-session', $user->id);

    expect($cart->user_id)->toBe($user->id)
        ->and($cart->session_id)->toBeNull();
});
