<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';
    protected $primaryKey = 'invoiceID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'issue_date',
        'invoice_number',
        'totalAmount',
        'bookingID',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'totalAmount' => 'decimal:2',
        ];
    }

    /**
     * Get the booking that owns this invoice.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}
