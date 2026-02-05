<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingPayment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'phone',
        'amount',
        'email',
        'movie_id',
        'checkout_request_id',
        'status',
        'mpesa_receipt_number',
        'transaction_date',
    ];
    
    public function movie()
        {
            return $this->belongsTo(Movie::class, 'movie_id');
        }

}


