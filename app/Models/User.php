<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Use 'user' table (singular) to match database
    protected $table = 'user';
    
    // Use userID as primary key to match database structure
    protected $primaryKey = 'userID';
    public $incrementing = true;
    protected $keyType = 'int';

    // Disable timestamps since they're stored as integers, not timestamps
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
            // Remove 'password' => 'hashed' to handle legacy passwords
        ];
    }

    /**
     * Get id attribute (maps from userID for compatibility).
     */
    public function getIdAttribute()
    {
        return $this->userID;
    }

    /**
     * Set id attribute (maps to userID for compatibility).
     */
    public function setIdAttribute($value)
    {
        $this->attributes['userID'] = $value;
    }

    /**
     * Get the customer record for this user.
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'userID', 'userID');
    }

    /**
     * Get the admin record for this user.
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'userID', 'userID');
    }

    /**
     * Get the staff record for this user.
     */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'userID', 'userID');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->admin()->exists() || $this->role == 1 || $this->role === 'admin';
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->customer()->exists() || $this->role == 0 || $this->role === 'customer';
    }

    /**
     * Check if user is a staff member.
     */
    public function isStaff(): bool
    {
        return $this->staff()->exists() || $this->role == 2 || $this->role === 'staff';
    }
}
