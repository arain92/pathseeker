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
        Schema::create('career_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->text('description');
            $table->string('icon', 60)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_domain_id')->constrained()->restrictOnDelete();
            $table->string('title', 150);
            $table->string('slug', 170)->unique();
            $table->text('summary');
            $table->longText('description');
            $table->json('responsibilities');
            $table->json('education_pathway');
            $table->string('education_level', 100)->index();
            $table->string('experience_level', 60)->index();
            $table->unsignedInteger('salary_min')->nullable()->index();
            $table->unsignedInteger('salary_max')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->string('salary_context', 255)->nullable();
            $table->string('demand_level', 30)->index();
            $table->text('future_outlook')->nullable();
            $table->string('icon', 60)->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedBigInteger('view_count')->default(0)->index();
            $table->timestamps();
            $table->index(['career_domain_id', 'is_published']);
            $table->index(['title', 'is_published']);
        });

        Schema::create('career_skills', function (Blueprint $table) {
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('importance', 20)->default('required');
            $table->unsignedTinyInteger('weight')->default(5);
            $table->primary(['career_id', 'skill_id']);
        });

        Schema::create('career_dimension_weights', function (Blueprint $table) {
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->string('dimension', 50);
            $table->decimal('weight', 4, 3);
            $table->primary(['career_id', 'dimension']);
        });

        Schema::create('career_interest_weights', function (Blueprint $table) {
            $table->foreignId('career_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 4, 3);
            $table->primary(['career_id', 'interest_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_interest_weights');
        Schema::dropIfExists('career_dimension_weights');
        Schema::dropIfExists('career_skills');
        Schema::dropIfExists('careers');
        Schema::dropIfExists('career_domains');
    }
};
