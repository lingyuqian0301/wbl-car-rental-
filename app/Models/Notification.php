<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notification';
    protected $primaryKey = 'notificationID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'recipientType',
        'message',
        'sent_date',
        'customerID',
        'staffID',
    ];

    protected function casts(): array
    {
        return [
            'sent_date' => 'datetime',
        ];
    }

    /**
     * Create a notification for staff.
     */
    public static function createForStaff(string $message, ?int $staffID = null): self
    {
        return self::create([
            'recipientType' => 'staff',
            'message' => $message,
            'sent_date' => now(),
            'staffID' => $staffID,
        ]);
    }

    /**
     * Create a notification for customer.
     */
    public static function createForCustomer(string $message, int $customerID): self
    {
        return self::create([
            'recipientType' => 'customer',
            'message' => $message,
            'sent_date' => now(),
            'customerID' => $customerID,
        ]);
    }
}

