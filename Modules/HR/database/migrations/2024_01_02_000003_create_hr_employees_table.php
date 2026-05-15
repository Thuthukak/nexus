<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('department_id')->nullable();
            $table->uuid('job_title_id')->nullable();
            $table->uuid('manager_id')->nullable();
            $table->enum('employment_type', [
                'full_time', 'part_time', 'contract', 'intern'
            ])->default('full_time');
            $table->enum('status', [
                'active', 'on_leave', 'suspended', 'terminated'
            ])->default('active');
            $table->string('employee_number')->unique()->nullable();
            $table->string('id_number')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('phone')->nullable();
            $table->json('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employees');
    }
};
