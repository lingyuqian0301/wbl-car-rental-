<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural "invoices"
    protected $table = 'invoice';

    // Specify the Primary Key
    protected $primaryKey = 'invoiceID';

    // Allow these fields to be filled by the approve() function
    protected $fillable = [
        'invoice_number',
        'issue_date',
        'totalAmount',
        'bookingID',
        'staffID'
    ];

    // Disable timestamps if your table doesn't have created_at/updated_at
    public $timestamps = false;
}