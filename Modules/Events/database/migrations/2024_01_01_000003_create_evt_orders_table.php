<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evt_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->uuid('event_id');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->uuid('invoice_id')->nullable();     // links to fin_invoices
            $table->uuid('customer_id')->nullable();    // links to fin_customers
            $table->enum('status', [
                'pending', 'paid', 'cancelled', 'refunded'
            ])->default('pending');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_total', 10, 2)->default(0);
            $table->decimal('total',    10, 2)->default(0);
            $table->string('payment_token', 64)->nullable()->unique();
            $table->timestamp('payment_token_expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id')
                  ->references('id')
                  ->on('evt_events')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('evt_orders'); }
};
