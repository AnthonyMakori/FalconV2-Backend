<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchandiseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'color',
        'size',
        'preferred_phone',
        'location',
        'additional_info',
    ];

    /**
     * Relation to the payment
     */
    public function payment()
    {
        return $this->belongsTo(MerchandisePayment::class, 'payment_id');
    }
}
