<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_assignment_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('assignment_id');
            $table->uuid('enrollment_id');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->unsignedInteger('grade')->nullable();   // out of max_marks
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['assignment_id', 'enrollment_id']);

            $table->foreign('assignment_id')->references('id')->on('lms_assignments')->cascadeOnDelete();
            $table->foreign('enrollment_id')->references('id')->on('lms_enrollments')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('lms_assignment_submissions'); }
};
