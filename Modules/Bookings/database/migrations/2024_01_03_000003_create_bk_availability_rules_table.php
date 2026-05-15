<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_availability_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('resource_id');
            $table->enum('day_of_week', [
                'monday','tuesday','wednesday','thursday','friday','saturday','sunday'
            ]);
            $table->time('open_time')->default('08:00:00');
            $table->time('close_time')->default('17:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_availability_rules');
    }
};
