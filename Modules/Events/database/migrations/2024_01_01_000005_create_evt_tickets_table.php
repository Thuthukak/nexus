<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evt_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_item_id');
            $table->uuid('order_id');
            $table->string('ticket_number')->unique();   // TKT-XXXX-XXXX
            $table->string('qr_data');                   // unique string for QR
            $table->enum('status', ['issued', 'used', 'cancelled'])->default('issued');
            $table->string('holder_name');
            $table->string('holder_email');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->foreign('order_item_id')
                  ->references('id')
                  ->on('evt_order_items')
                  ->cascadeOnDelete();

            $table->foreign('order_id')
                  ->references('id')
                  ->on('evt_orders')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('evt_tickets'); }
};
