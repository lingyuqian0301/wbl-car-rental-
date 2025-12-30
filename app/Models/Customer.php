<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
 protected $primaryKey = 'customerID';

    protected $fillable = [
        'user_id',
        'fullname',
        'email',
        'registration_date',
        'customer_type',
    ];

    public $timestamps = false; // since your table has no created_at/updated_at

    //
    public function user()
{
    return $this->belongsTo(User::class);
}


}
