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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->text('description');
            $table->string('type', 30)->index();
            $table->string('audience', 40)->index();
            $table->string('category', 80)->index();
            $table->string('file_url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->unsignedBigInteger('download_count')->default(0)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
            $table->index(['is_published', 'created_at']);
        });
        Schema::create('resource_tag', function (Blueprint $table) {
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['resource_id', 'tag_id']);
        });
        Schema::create('multimedia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->text('description');
            $table->string('type', 30)->index();
            $table->string('source_url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->longText('transcript')->nullable();
            $table->string('category', 80)->index();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedBigInteger('view_count')->default(0)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
        Schema::create('multimedia_tag', function (Blueprint $table) {
            $table->foreignId('multimedia_id')->constrained('multimedia')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['multimedia_id', 'tag_id']);
        });
        Schema::create('career_multimedia', function (Blueprint $table) {
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->foreignId('multimedia_id')->constrained('multimedia')->cascadeOnDelete();
            $table->primary(['career_id', 'multimedia_id']);
        });
        Schema::create('media_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('multimedia_id')->constrained('multimedia')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->timestamps();
            $table->unique(['user_id', 'multimedia_id']);
        });
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('person_name', 120);
            $table->string('slug', 180)->unique();
            $table->string('career_title', 160);
            $table->string('domain', 100)->index();
            $table->text('summary');
            $table->json('timeline');
            $table->text('advice');
            $table->string('avatar_url', 500)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('moderation_notes')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('success_stories');
        Schema::dropIfExists('media_ratings');
        Schema::dropIfExists('career_multimedia');
        Schema::dropIfExists('multimedia_tag');
        Schema::dropIfExists('multimedia');
        Schema::dropIfExists('resource_tag');
        Schema::dropIfExists('resources');
        Schema::dropIfExists('tags');
    }
};
