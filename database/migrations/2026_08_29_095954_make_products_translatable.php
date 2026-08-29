<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name', 'name_old');
            $table->renameColumn('slug', 'slug_old');
            $table->renameColumn('description', 'description_old');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->json('name')->nullable();
            $table->json('slug')->nullable();
            $table->json( 'description')->nullable();
        });


        DB::statement("UPDATE products SET name = json_build_object('uk', name_old)");
        DB::statement("UPDATE products SET slug = json_build_object('uk', slug_old)");
        DB::statement("UPDATE products SET description = json_build_object('uk', description_old)");
        DB::statement("CREATE UNIQUE INDEX products_slug_uk_unique ON products ((slug->>'uk'))");
        DB::statement("CREATE UNIQUE INDEX products_slug_ru_unique ON products ((slug->>'ru'))");

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('name_old');
            $table->dropColumn('slug_old');
            $table->dropColumn('description_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_old')->nullable();
            $table->string('slug_old')->nullable();
            $table->text('description_old')->nullable();
        });

        DB::statement("UPDATE products SET name_old = name->>'uk'");
        DB::statement("UPDATE products SET slug_old = slug->>'uk'");
        DB::statement("UPDATE products SET description_old = description->>'uk'");

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('slug');
            $table->dropColumn('description');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name_old', 'name');
            $table->renameColumn('slug_old', 'slug');
            $table->renameColumn('description_old', 'description');
        });
    }
};
