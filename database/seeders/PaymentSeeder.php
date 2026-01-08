<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        PaymentMethod::insert([
            [
                'name' => 'M-PESA',
                'slug' => 'mpesa',
                'description' => 'Mobile money payment method',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Flutterwave',
                'slug' => 'flutterwave',
                'description' => 'Card and bank payments',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'description' => 'International payments',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
