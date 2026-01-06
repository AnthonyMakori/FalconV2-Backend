<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'poster',
        'description',
        'location',
        'price',
        'type',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
    ];
}



