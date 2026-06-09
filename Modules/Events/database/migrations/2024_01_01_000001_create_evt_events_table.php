<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evt_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->string('venue')->nullable();
            $table->string('venue_address')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('banner_path')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])
                  ->default('draft');
            $table->unsignedInteger('max_capacity')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('organiser_name')->nullable();
            $table->string('organiser_email')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('evt_events'); }
};
