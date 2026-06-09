<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lesson_id')->unique();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedInteger('pass_mark')->default(70);        // percentage
            $table->unsignedInteger('max_attempts')->default(3);      // real attempts
            $table->boolean('allow_practice')->default(true);
            $table->unsignedInteger('time_limit_minutes')->nullable(); // null = no limit
            $table->boolean('show_answers_after')->default(true);     // show correct answers after attempt
            $table->boolean('randomise_questions')->default(false);
            $table->timestamps();

            $table->foreign('lesson_id')->references('id')->on('lms_lessons')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('lms_quizzes'); }
};
