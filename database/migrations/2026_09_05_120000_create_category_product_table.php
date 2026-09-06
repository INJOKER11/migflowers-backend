<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'category_id']);
        });

        DB::statement('INSERT INTO category_product (product_id, category_id) SELECT id, category_id FROM products');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();
        });

        DB::statement(<<<'SQL'
            UPDATE products SET category_id = first_category.category_id
            FROM (
                SELECT DISTINCT ON (product_id) product_id, category_id
                FROM category_product
                ORDER BY product_id, category_id
            ) AS first_category
            WHERE first_category.product_id = products.id
        SQL);

        Schema::dropIfExists('category_product');
    }
};
