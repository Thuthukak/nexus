<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', ['room', 'equipment', 'staff', 'vehicle', 'other'])
                  ->default('room');
            $table->unsignedInteger('capacity')->default(1);
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('colour', 7)->default('#3B82F6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_resources');
    }
};
