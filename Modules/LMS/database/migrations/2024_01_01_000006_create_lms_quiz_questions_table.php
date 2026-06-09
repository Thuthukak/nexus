<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_quiz_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quiz_id');
            $table->text('question');
            $table->enum('type', ['multiple_choice', 'true_false'])->default('multiple_choice');
            $table->json('options');          // array of option strings
            $table->string('correct_answer'); // index (0,1,2,3) or 'true'/'false'
            $table->text('explanation')->nullable(); // shown after attempt
            $table->unsignedInteger('marks')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('lms_quizzes')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('lms_quiz_questions'); }
};
