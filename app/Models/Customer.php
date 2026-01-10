<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $table = 'customer';
 protected $primaryKey = 'customerID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'userID',
        'phone_number',
        'address',
        'customer_license',
        'customer_license_img',
        'customer_ic_img',
        'emergency_contact',
        'booking_times',
        'default_bank_name',
        'default_account_no',
    ];

    /**
     * Get the user that owns this customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    /**
     * Get the local record for this customer.
     */
    public function local(): HasOne
    {
        return $this->hasOne(Local::class, 'customerID', 'customerID');
    }

    /**
     * Get the local student record for this customer.
     */
    public function localStudent(): HasOne
    {
        return $this->hasOne(LocalStudent::class, 'customerID', 'customerID');
    }

    /**
     * Get the local UTM staff record for this customer.
     */
    public function localUtmStaff(): HasOne
    {
        return $this->hasOne(Local_UTMStaff::class, 'customerID', 'customerID');
    }

    /**
     * Get the international record for this customer.
     */
    public function international(): HasOne
    {
        return $this->hasOne(International::class, 'customerID', 'customerID');
    }

    /**
     * Get the international student record for this customer.
     */
    public function internationalStudent(): HasOne
    {
        return $this->hasOne(InternationalStudent::class, 'customerID', 'customerID');
    }

    /**
     * Get the international UTM staff record for this customer.
     */
    public function internationalUtmStaff(): HasOne
    {
        return $this->hasOne(International_UTMStaff::class, 'customerID', 'customerID');
    }

    /**
     * Get the student detail record for this customer.
     * NOTE: This relationship is commented out because the studentdetails table doesn't exist in the schema.
     * Student information is accessed via LocalStudent->studentDetails or InternationalStudent->studentDetails
     */
    // public function studentDetail(): HasOne
    // {
    //     return $this->hasOne(StudentDetail::class, 'customerID', 'customerID');
    // }

    /**
     * Get the bookings for this customer.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customerID', 'customerID');
    }

    /**
     * Get the wallet account for this customer.
     */
    public function walletAccount(): HasOne
    {
        return $this->hasOne(WalletAccount::class, 'customerID', 'customerID');
    }

    /**
     * Get the loyalty card for this customer.
     */
    public function loyaltyCard(): HasOne
    {
        return $this->hasOne(LoyaltyCard::class, 'customerID', 'customerID');
    }

    /**
     * Get browse history for this customer.
     */
    public function browseHistory(): HasMany
    {
        return $this->hasMany(BrowseHistory::class, 'customerID', 'customerID');
    }

    /**
     * Get the route key for the model (for route model binding).
     */
    public function getRouteKeyName(): string
    {
        return 'customerID';
    }
}
