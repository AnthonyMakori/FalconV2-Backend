<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    use HasFactory;

  protected $fillable = [
    'phone',
    'amount',
    'email',
    'event_id',
    'checkout_request_id',
    'ticket_code',
    'status',
    'attendee_name',
    'mpesa_receipt_number',
    'transaction_date',
];

}
