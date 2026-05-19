<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->boolean('deposit_required')->default(false)->after('notes');
            $table->decimal('deposit_percentage', 5, 2)->default(50)->after('deposit_required');
            $table->decimal('deposit_amount', 15, 2)->default(0)->after('deposit_percentage');
            $table->timestamp('deposit_paid_at')->nullable()->after('deposit_amount');
        });
    }

    public function down(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->dropColumn(['deposit_required', 'deposit_percentage', 'deposit_amount', 'deposit_paid_at']);
        });
    }
};
