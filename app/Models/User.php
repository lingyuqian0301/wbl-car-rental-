<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'matric_number',
        'identification_card',
        'college',
        'faculty',
        'program',
        'address',
        'city',
        'region',
        'postcode',
        'state',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
     * Get the bookings for the user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the wallet account for the user.
     */
    public function walletAccount()
    {
        return $this->hasOne(\App\Models\WalletAccount::class);
    }

    /**
     * Get or create wallet account for the user.
     */
    public function getOrCreateWalletAccount(): \App\Models\WalletAccount
    {
        return $this->walletAccount ?? \App\Models\WalletAccount::create([
            'user_id' => $this->id,
            'virtual_balance' => 0.00,
            'available_balance' => 0.00,
        ]);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if user is a staff member.
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }
}
