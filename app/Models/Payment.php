<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

  

    // Mass assignable attributes
    protected $fillable = [
        'email',
        'mpesa_receipt_number',
        'amount',
        'status',
        'checkout_request_id',
        'created_at',
        'updated_at',
    ];

    // Optionally, cast data types
    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
