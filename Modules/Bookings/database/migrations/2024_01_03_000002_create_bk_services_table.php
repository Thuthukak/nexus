<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->unsignedInteger('buffer_minutes')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('max_participants')->default(1);
            $table->boolean('requires_confirmation')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_services');
    }
};
