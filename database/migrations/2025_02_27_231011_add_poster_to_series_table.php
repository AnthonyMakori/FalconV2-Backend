<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
{
    if (!Schema::hasColumn('series', 'poster')) {
        Schema::table('series', function (Blueprint $table) {
            $table->string('poster')->nullable()->after('release_date');
        });
    }
}

    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('poster');
        });
    }
};
