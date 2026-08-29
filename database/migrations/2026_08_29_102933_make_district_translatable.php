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
        Schema::table('districts', function (Blueprint $table) {
            $table->renameColumn('name', 'name_old');
            $table->renameColumn('description', 'description_old');
        });
        Schema::table('districts', function (Blueprint $table) {
            $table->json('name')->nullable();
            $table->json( 'description')->nullable();
        });


        DB::statement("UPDATE districts SET name = json_build_object('uk', name_old)");
        DB::statement("UPDATE districts SET description = json_build_object('uk', description_old)");

        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn('name_old');
            $table->dropColumn('description_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('districts', function (Blueprint $table) {
            $table->string('name_old')->nullable();
            $table->text('description_old')->nullable();
        });

        DB::statement("UPDATE districts SET name_old = name->>'uk'");
        DB::statement("UPDATE districts SET description_old = description->>'uk'");

        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
        });
        Schema::table('districts', function (Blueprint $table) {
            $table->renameColumn('name_old', 'name');
            $table->renameColumn('description_old', 'description');
        });
    }
};
