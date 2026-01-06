<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('merchandises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->integer('stock');
            $table->enum('status', ['In Stock', 'Out of Stock'])->default('In Stock');
            $table->string('image')->nullable(); // For storing image path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchandises');
    }
};
