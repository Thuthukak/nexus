<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_payslips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month'); // 1-12
            $table->string('file_path');
            $table->string('file_name');
            $table->decimal('gross_amount', 15, 2)->nullable();
            $table->decimal('net_amount',   15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')
                  ->references('id')
                  ->on('hr_employees')
                  ->cascadeOnDelete();

            $table->unique(['employee_id', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_payslips');
    }
};
