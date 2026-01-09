<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeasingExpense extends Model
{
    protected $table = 'leasing_expenses';
    
    protected $fillable = [
        'expense_type',
        'description',
        'amount',
        'expense_date',
        'vehicle_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }
}












