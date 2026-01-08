<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
{
    Schema::table('movies', function (Blueprint $table) {

        // New columns (safe on MariaDB)
        if (!Schema::hasColumn('movies', 'poster_path')) {
            $table->string('poster_path')->nullable();
        }

        if (!Schema::hasColumn('movies', 'movie_path')) {
            $table->string('movie_path')->nullable();
        }

        if (!Schema::hasColumn('movies', 'purchase_price')) {
            $table->decimal('purchase_price', 8, 2)->nullable();
        }

        if (!Schema::hasColumn('movies', 'release_year')) {
            $table->year('release_year')->nullable();
        }
    });

    // Copy existing data
    DB::statement('UPDATE movies SET poster_path = poster WHERE poster_path IS NULL');
    DB::statement('UPDATE movies SET movie_path = movie WHERE movie_path IS NULL');
    DB::statement('UPDATE movies SET purchase_price = price WHERE purchase_price IS NULL');
    DB::statement('UPDATE movies SET release_year = date_released WHERE release_year IS NULL');

    // Drop old columns
    Schema::table('movies', function (Blueprint $table) {
        if (Schema::hasColumn('movies', 'poster')) {
            $table->dropColumn('poster');
        }

        if (Schema::hasColumn('movies', 'movie')) {
            $table->dropColumn('movie');
        }

        if (Schema::hasColumn('movies', 'price')) {
            $table->dropColumn('price');
        }

        if (Schema::hasColumn('movies', 'date_released')) {
            $table->dropColumn('date_released');
        }

        // Add remaining new fields
        $table->integer('duration')->nullable();
        $table->string('language')->nullable();
        $table->string('genre')->nullable();

        $table->enum('status', ['published', 'draft', 'archived'])
              ->default('draft');

        $table->string('trailer_path')->nullable();
        $table->decimal('rental_price', 8, 2)->nullable();
        $table->integer('rental_period')->nullable();
        $table->boolean('free_preview')->default(false);
        $table->integer('preview_duration')->nullable();

        $table->string('seo_title')->nullable();
        $table->text('seo_description')->nullable();
        $table->string('seo_keywords')->nullable();
    });
}

  public function down(): void
{
    Schema::table('movies', function (Blueprint $table) {

        $table->string('poster')->nullable();
        $table->string('movie')->nullable();
        $table->decimal('price', 8, 2)->nullable();
        $table->date('date_released')->nullable();
    });

    DB::statement('UPDATE movies SET poster = poster_path');
    DB::statement('UPDATE movies SET movie = movie_path');
    DB::statement('UPDATE movies SET price = purchase_price');
    DB::statement('UPDATE movies SET date_released = release_year');

    Schema::table('movies', function (Blueprint $table) {
        $table->dropColumn([
            'poster_path',
            'movie_path',
            'purchase_price',
            'release_year',
            'duration',
            'language',
            'genre',
            'status',
            'trailer_path',
            'rental_price',
            'rental_period',
            'free_preview',
            'preview_duration',
            'seo_title',
            'seo_description',
            'seo_keywords',
        ]);
    });
}

};
