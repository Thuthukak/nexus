<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fin_quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->uuid('customer_id');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', [
                'draft', 'sent', 'accepted', 'declined', 'expired', 'converted'
            ])->default('draft');
            $table->date('issue_date');
            $table->date('valid_until');
            $table->string('net_terms')->default('custom');
            $table->string('currency', 3)->default('ZAR');
            $table->decimal('subtotal',  15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total',     15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();       // terms and conditions
            $table->string('quote_token', 64)->nullable()->unique();
            $table->timestamp('quote_token_expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->uuid('converted_invoice_id')->nullable(); // set when converted
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
        Schema::dropIfExists('fin_quotations');
    }
};
