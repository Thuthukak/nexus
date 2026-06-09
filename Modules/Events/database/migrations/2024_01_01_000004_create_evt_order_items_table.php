<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evt_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('ticket_type_id');
            $table->string('ticket_type_name');   // snapshot at time of purchase
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal',   10, 2);
            $table->timestamps();

            $table->foreign('order_id')
                  ->references('id')
                  ->on('evt_orders')
                  ->cascadeOnDelete();

            $table->foreign('ticket_type_id')
                  ->references('id')
                  ->on('evt_ticket_types')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('evt_order_items'); }
};
