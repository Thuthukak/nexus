<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('section_id');
            $table->string('title');
            $table->enum('type', ['video', 'text', 'file', 'quiz'])->default('text');
            $table->longText('content')->nullable();       // rich text or embed URL
            $table->string('video_url')->nullable();       // YouTube/Vimeo URL
            $table->string('video_type')->nullable();      // 'embed' or 'upload'
            $table->string('video_path')->nullable();      // for uploaded videos
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_free_preview')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('section_id')->references('id')->on('lms_sections')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('lms_lessons'); }
};
