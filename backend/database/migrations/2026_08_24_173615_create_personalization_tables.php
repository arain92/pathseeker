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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bookmarkable_type', 100);
            $table->unsignedBigInteger('bookmarkable_id');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id']);
            $table->index(['bookmarkable_type', 'bookmarkable_id']);
        });

        Schema::create('recent_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('viewable_type', 100);
            $table->unsignedBigInteger('viewable_id');
            $table->dateTime('first_viewed_at');
            $table->dateTime('last_viewed_at');
            $table->unsignedInteger('view_count')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'viewable_type', 'viewable_id']);
            $table->index(['viewable_type', 'viewable_id', 'last_viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_views');
        Schema::dropIfExists('bookmarks');
    }
};
