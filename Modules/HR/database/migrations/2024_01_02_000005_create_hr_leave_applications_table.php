<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_leave_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('leave_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('days_requested')->default(1);
            $table->enum('status', [
                'pending', 'approved', 'rejected', 'cancelled'
            ])->default('pending');
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')
                  ->references('id')
                  ->on('hr_employees')
                  ->cascadeOnDelete();

            $table->foreign('leave_type_id')
                  ->references('id')
                  ->on('hr_leave_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_leave_applications');
    }
};
