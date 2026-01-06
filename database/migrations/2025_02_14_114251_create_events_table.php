<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->string('poster')->nullable();
            $table->text('description');
            $table->string('location');
            $table->decimal('price', 8, 2)->nullable();
            $table->timestamps();
        });
    }
    

    public function down(): void {
        Schema::dropIfExists('events');
    }
};
