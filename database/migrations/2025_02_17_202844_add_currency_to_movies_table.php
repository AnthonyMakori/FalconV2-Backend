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
    Schema::table('movies', function (Blueprint $table) {
        if (!Schema::hasColumn('movies', 'currency')) {
            $table->string('currency', 3)->default('USD')->nullable(false);
        }
        // Add currency column
    });
}

public function down()
{
    Schema::table('movies', function (Blueprint $table) {
        $table->dropColumn('currency'); 
    });
}

};
