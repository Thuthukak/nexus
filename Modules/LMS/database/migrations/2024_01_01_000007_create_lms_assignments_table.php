<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_id');
            $table->uuid('section_id')->nullable();
            $table->string('title');
            $table->longText('description');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('max_marks')->default(100);
            $table->boolean('is_required')->default(false); // required for certificate
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('course_id')->references('id')->on('lms_courses')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('lms_sections')->nullOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('lms_assignments'); }
};
