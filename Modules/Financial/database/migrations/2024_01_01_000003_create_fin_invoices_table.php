<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fin_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->uuid('customer_id');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', [
                'draft','approved','sent','part_paid','paid','overdue','cancelled'
            ])->default('draft');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('currency', 3)->default('ZAR');
            $table->decimal('subtotal',  15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total',     15, 2)->default(0);
            $table->decimal('paid_total',15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')
                  ->references('id')
                  ->on('fin_customers')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fin_invoices');
    }
};
