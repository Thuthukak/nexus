<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_quiz_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('enrollment_id');
            $table->uuid('quiz_id');
            $table->json('answers');              // {question_id: answer}
            $table->unsignedInteger('score');     // percentage
            $table->unsignedInteger('marks_earned');
            $table->unsignedInteger('marks_total');
            $table->boolean('passed')->default(false);
            $table->boolean('is_practice')->default(false);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('enrollment_id')->references('id')->on('lms_enrollments')->cascadeOnDelete();
            $table->foreign('quiz_id')->references('id')->on('lms_quizzes')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('lms_quiz_attempts'); }
};
