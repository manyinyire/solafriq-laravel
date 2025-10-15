<?php

use App\Models\User;
use App\Models\Order;
use App\Models\InstallmentPlan;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can be created with required fields', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and($user->exists)->toBeTrue();
});

test('user password is hashed', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    expect($user->password)->not->toBe('password123')
        ->and(password_verify('password123', $user->password))->toBeTrue();
});

test('user can check if they are admin', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $client = User::factory()->create(['role' => 'CLIENT']);

    expect($admin->isAdmin())->toBeTrue()
        ->and($client->isAdmin())->toBeFalse();
});

test('user can check if they are client', function () {
    $admin = User::factory()->create(['role' => 'ADMIN']);
    $client = User::factory()->create(['role' => 'CLIENT']);

    expect($client->isClient())->toBeTrue()
        ->and($admin->isClient())->toBeFalse();
});

test('user has orders relationship', function () {
    $user = User::factory()->create();
    
    expect($user->orders())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user has installment plans relationship', function () {
    $user = User::factory()->create();
    
    expect($user->installmentPlans())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user has warranties relationship', function () {
    $user = User::factory()->create();
    
    expect($user->warranties())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user email must be unique', function () {
    User::factory()->create(['email' => 'test@example.com']);
    
    expect(fn() => User::factory()->create(['email' => 'test@example.com']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
