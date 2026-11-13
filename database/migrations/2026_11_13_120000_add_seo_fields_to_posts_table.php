<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional per-locale SEO overrides for a journal article. Same JSON
     * shape as the other translatable post columns ({"uk": …, "ru": …});
     * nullable, so every existing post keeps working with none set.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('seo_title')->nullable();
            $table->json('meta_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'meta_description']);
        });
    }
};
