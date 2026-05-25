<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installed_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();          // Core, Financial, HR, Bookings
            $table->string('alias')->unique();          // core, financial, hr, bookings
            $table->string('version')->default('1.0.0');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_licensed')->default(false);
            $table->boolean('is_core')->default(false); // Core cannot be disabled
            $table->json('metadata')->nullable();        // description, requires, icon etc
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('licensed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installed_modules');
    }
};
