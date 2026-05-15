<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->uuid('service_id');
            $table->uuid('resource_id')->nullable();
            $table->foreignId('customer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->enum('status', [
                'enquiry','pending','confirmed',
                'in_progress','completed','cancelled','no_show'
            ])->default('pending');
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->boolean('deposit_paid')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_bookings');
    }
};
