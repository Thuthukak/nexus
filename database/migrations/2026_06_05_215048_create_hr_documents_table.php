<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('name');
            $table->string('type');           // contract, id_document, certificate, tax_form, medical, nda, sla, other
            $table->string('file_path');      // storage/app/private/hr/documents/...
            $table->string('file_name');      // original filename
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->enum('visibility', ['web', 'customer'])->default('web');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->boolean('expiry_notified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')
                  ->references('id')
                  ->on('hr_employees')
                  ->nullOnDelete();

            $table->foreign('customer_id')
                  ->references('id')
                  ->on('fin_customers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_documents');
    }
};
