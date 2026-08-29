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
        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('title', 'title_old');
            $table->renameColumn('slug', 'slug_old');
            $table->renameColumn('content', 'content_old');
            $table->renameColumn('subject', 'subject_old');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->json('title')->nullable();
            $table->json('slug')->nullable();
            $table->json( 'content')->nullable();
            $table->json( 'subject')->nullable();
        });


        DB::statement("UPDATE posts SET title = json_build_object('uk', title_old)");
        DB::statement("UPDATE posts SET slug = json_build_object('uk', slug_old)");
        DB::statement("UPDATE posts SET content = json_build_object('uk', content_old)");
        DB::statement("UPDATE posts SET subject = json_build_object('uk', subject_old)");
        DB::statement("CREATE UNIQUE INDEX posts_slug_uk_unique ON posts ((slug->>'uk'))");
        DB::statement("CREATE UNIQUE INDEX posts_slug_ru_unique ON posts ((slug->>'ru'))");

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('title_old');
            $table->dropColumn('slug_old');
            $table->dropColumn('content_old');
            $table->dropColumn('subject_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title_old')->nullable();
            $table->string('slug_old')->nullable();
            $table->text('content_old')->nullable();
            $table->text('subject_old')->nullable();
        });

        DB::statement("UPDATE posts SET title_old = title->>'uk'");
        DB::statement("UPDATE posts SET slug_old = slug->>'uk'");
        DB::statement("UPDATE posts SET content_old = content->>'uk'");
        DB::statement("UPDATE posts SET subject_old = subject->>'uk'");

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('slug');
            $table->dropColumn('content');
            $table->dropColumn('subject');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('title_old', 'title');
            $table->renameColumn('slug_old', 'slug');
            $table->renameColumn('content_old', 'content');
            $table->renameColumn('subject_old', 'subject');
        });
    }
};
