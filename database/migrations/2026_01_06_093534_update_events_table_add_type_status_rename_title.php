<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {

            if (!Schema::hasColumn('events', 'type')) {
                $table->string('type')->after('location');
            }

            if (!Schema::hasColumn('events', 'status')) {
                $table->enum('status', ['Upcoming', 'Completed', 'Cancelled'])
                      ->default('Upcoming')
                      ->after('type');
            }
        });

        // Rename title → name ONLY if name does not exist
        if (
            Schema::hasColumn('events', 'title') &&
            !Schema::hasColumn('events', 'name')
        ) {
            DB::statement('ALTER TABLE events CHANGE title name VARCHAR(255)');
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('events', 'status')) {
                $table->dropColumn('status');
            }
        });

        if (
            Schema::hasColumn('events', 'name') &&
            !Schema::hasColumn('events', 'title')
        ) {
            DB::statement('ALTER TABLE events CHANGE name title VARCHAR(255)');
        }
    }
};
