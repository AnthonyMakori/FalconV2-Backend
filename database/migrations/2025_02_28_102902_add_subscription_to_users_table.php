<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('subscription')->default(false); // Add subscription column
            $table->decimal('total_purchases', 10, 2)->default(0.00); // Add total_purchases column
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subscription');
            $table->dropColumn('total_purchases');
        });
    }
};
