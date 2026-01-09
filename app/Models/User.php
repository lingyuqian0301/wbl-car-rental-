<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'userID'; 
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'email',
        'phone',
        'name',
        'lastLogin',
        'dateRegistered',
        'DOB',
        'age',
        'isActive',
        // Added for Bank Details
        'account_no',
        'account_type', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'lastLogin' => 'datetime',
            'dateRegistered' => 'datetime',
            'DOB' => 'date',
            'age' => 'integer',
            'isActive' => 'boolean',
            'role' => 'integer',
        ];
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'userID', 'userID');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'userID', 'userID');
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'userID', 'userID');
    }

    public function isAdmin(): bool
    {
        return $this->admin()->exists() || $this->role == 1 || $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->customer()->exists() || $this->role == 0 || $this->role === 'customer';
    }

    public function isStaff(): bool
    {
        return $this->staff()->exists() || $this->role == 2 || $this->role === 'staff';
    }
}