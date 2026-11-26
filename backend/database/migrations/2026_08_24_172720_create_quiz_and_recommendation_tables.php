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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->text('prompt');
            $table->string('section', 60)->index();
            $table->string('type', 30)->default('likert');
            $table->unsignedInteger('sort_order')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->string('label', 255);
            $table->string('value', 80);
            $table->json('dimension_weights');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('in_progress')->index();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();
        });
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_option_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['quiz_attempt_id', 'quiz_question_id']);
        });
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('dimension_scores');
            $table->json('strongest_traits');
            $table->json('recommended_domains');
            $table->string('scoring_version', 30)->default('v1');
            $table->timestamps();
        });
        Schema::create('career_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rank');
            $table->decimal('overall_score', 5, 2);
            $table->json('factors');
            $table->json('explanation');
            $table->timestamps();
            $table->unique(['quiz_attempt_id', 'career_id']);
            $table->unique(['quiz_attempt_id', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_recommendations');
        Schema::dropIfExists('quiz_results');
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
    }
};
