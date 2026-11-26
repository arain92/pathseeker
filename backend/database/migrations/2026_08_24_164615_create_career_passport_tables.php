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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('country', 100)->nullable();
            $table->string('education_level', 100)->nullable();
            $table->string('current_field', 150)->nullable();
            $table->string('degree', 150)->nullable();
            $table->string('field_of_study', 150)->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->string('job_title', 150)->nullable();
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->string('industry', 150)->nullable();
            $table->text('career_goal')->nullable();
            $table->text('transition_goal')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('resume_path')->nullable();
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::create('user_skills', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('proficiency')->default(1);
            $table->timestamps();
            $table->primary(['user_id', 'skill_id']);
        });

        Schema::create('user_interests', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('strength')->default(1);
            $table->timestamps();
            $table->primary(['user_id', 'interest_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_interests');
        Schema::dropIfExists('user_skills');
        Schema::dropIfExists('interests');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('user_profiles');
    }
};
