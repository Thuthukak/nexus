<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            // EFT hold expiry — set when invoice is created with gateway=none
            $table->timestamp('eft_hold_expires_at')->nullable()->after('payment_token_expires_at');

            // Proof of payment
            $table->string('pop_path')->nullable()->after('eft_hold_expires_at');
            $table->string('pop_original_name')->nullable()->after('pop_path');
            $table->timestamp('pop_uploaded_at')->nullable()->after('pop_original_name');
            $table->text('pop_notes')->nullable()->after('pop_uploaded_at');

            // Admin review
            $table->enum('pop_status', ['none', 'pending', 'approved', 'rejected'])
                  ->default('none')->after('pop_notes');
            $table->text('pop_rejection_reason')->nullable()->after('pop_status');
            $table->foreignId('pop_reviewed_by')->nullable()
                  ->constrained('users')->nullOnDelete()->after('pop_rejection_reason');
            $table->timestamp('pop_reviewed_at')->nullable()->after('pop_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'eft_hold_expires_at',
                'pop_path', 'pop_original_name', 'pop_uploaded_at', 'pop_notes',
                'pop_status', 'pop_rejection_reason', 'pop_reviewed_by', 'pop_reviewed_at',
            ]);
        });
    }
};