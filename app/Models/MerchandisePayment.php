<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchandisePayment extends Model
{
    protected $fillable = [
        'phone',
        'email',
        'merchandise_id',
        'checkout_request_id',
        'color',
        'size',
        'referral_id',
        'status',
        'mpesa_receipt_number',
        'transaction_date',
        'amount',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];
}
