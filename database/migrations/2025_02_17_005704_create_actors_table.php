<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('actors', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->integer('movies');
        $table->integer('series');
        $table->string('role');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('actors');
}

};
