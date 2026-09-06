<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE categories ALTER COLUMN name TYPE jsonb USING name::jsonb');
        DB::statement('ALTER TABLE categories ALTER COLUMN slug TYPE jsonb USING slug::jsonb');
        DB::statement('ALTER TABLE categories ALTER COLUMN description TYPE jsonb USING description::jsonb');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE categories ALTER COLUMN name TYPE json USING name::json');
        DB::statement('ALTER TABLE categories ALTER COLUMN slug TYPE json USING slug::json');
        DB::statement('ALTER TABLE categories ALTER COLUMN description TYPE json USING description::json');
    }
};
