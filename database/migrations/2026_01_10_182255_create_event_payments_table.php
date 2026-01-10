<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_payments', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->decimal('amount', 10, 2);
            $table->string('email');
            $table->foreignId('event_id')->constrained()->onDelete('cascade'); 
            $table->string('checkout_request_id');
            $table->string('ticket_code')->nullable();
            $table->string('status')->default('Pending'); 
            $table->string('attendee_name')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_payments');
    }
};
