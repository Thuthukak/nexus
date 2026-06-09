<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_lesson_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('enrollment_id');
            $table->uuid('lesson_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamps();

            $table->unique(['enrollment_id', 'lesson_id']);

            $table->foreign('enrollment_id')->references('id')->on('lms_enrollments')->cascadeOnDelete();
            $table->foreign('lesson_id')->references('id')->on('lms_lessons')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('lms_lesson_progress'); }
};
