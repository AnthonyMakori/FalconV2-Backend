<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
// database/migrations/xxxx_xx_xx_create_movies_table.php
public function up()
{
    Schema::create('movies', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('category');
        $table->text('description');
        $table->decimal('price', 10, 2);
        $table->string('currency', 3);
        $table->string('poster')->nullable();
        $table->timestamps();
        $table->string('movie');
    });
}



    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};

