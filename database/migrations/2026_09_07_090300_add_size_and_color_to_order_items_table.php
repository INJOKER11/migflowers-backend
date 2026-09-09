<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('size_id')->nullable()->after('product_id')->constrained('sizes')->restrictOnDelete();
            $table->foreignId('product_color_id')->nullable()->after('size_id')->constrained('product_colors')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('size_id');
            $table->dropConstrainedForeignId('product_color_id');
        });
    }
};
