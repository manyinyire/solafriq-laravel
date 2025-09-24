<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'address',
        'role',
        'image',
        'new_email',
        'email_verification_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    /**
     * Check if user is client
     */
    public function isClient(): bool
    {
        return $this->role === 'CLIENT';
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the installment plans for the user.
     */
    public function installmentPlans(): HasMany
    {
        return $this->hasMany(InstallmentPlan::class);
    }

    /**
     * Get the warranties for the user.
     */
    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    /**
     * Get the warranty claims for the user.
     */
    public function warrantyClaims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    /**
     * Get the custom systems for the user.
     */
    public function customSystems(): HasMany
    {
        return $this->hasMany(CustomSystem::class);
    }

    /**
     * Get the invoices for the user.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}