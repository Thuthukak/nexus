<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fin_recurring_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('source_invoice_id');   // invoice used as template
            $table->uuid('customer_id');
            $table->string('frequency');          // daily, weekly, monthly, quarterly, yearly
            $table->unsignedInteger('interval')->default(1); // every N frequencies
            $table->date('start_date');
            $table->date('end_date')->nullable();  // null = runs indefinitely
            $table->unsignedInteger('max_occurrences')->nullable(); // null = unlimited
            $table->unsignedInteger('occurrences_count')->default(0);
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->boolean('auto_send')->default(true); // auto email on creation
            $table->unsignedInteger('due_days')->default(30); // days until due on generated invoice
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_invoice_id')
                  ->references('id')
                  ->on('fin_invoices');

            $table->foreign('customer_id')
                  ->references('id')
                  ->on('fin_customers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fin_recurring_invoices');
    }
};
