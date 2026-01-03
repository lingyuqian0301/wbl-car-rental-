<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'User';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'userID';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'lastLogin' => 'datetime',
            'dateRegistered' => 'datetime',
            'DOB' => 'date',
            'isActive' => 'boolean',
        ];
    }

    /**
     * Get the customer profile for the user.
     * Using direct query to avoid Laravel's snake_case convention for foreign keys.
     */
    public function getCustomerAttribute()
    {
        return Customer::where('userID', $this->userID)->first();
    }

    /**
     * Get the customer relationship (for eager loading).
     */
    public function customerRelation()
    {
        return $this->hasOne(Customer::class, 'userID', 'userID');
    }

    /**
     * Get the staff profile for the user.
     */
    public function staff()
    {
        return $this->hasOne(Staff::class, 'userID', 'userID');
    }

    /**
     * Get the admin profile for the user.
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'userID', 'userID');
    }

    /**
     * Get the wallet account for the user (through customer).
     */
    public function walletAccount()
    {
        return $this->hasOneThrough(
            WalletAccount::class,
            Customer::class,
            'userID',
            'customerID',
            'userID',
            'customerID'
        );
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return Admin::where('userID', $this->userID)->exists();
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return Customer::where('userID', $this->userID)->exists();
    }

    /**
     * Check if user is a staff member.
     */
    public function isStaff(): bool
    {
        return Staff::where('userID', $this->userID)->exists();
    }
}
