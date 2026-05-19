<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->timestamp('receipt_sent_at')->nullable()->after('paid_total');
            $table->timestamp('last_sent_at')->nullable()->after('receipt_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->dropColumn(['receipt_sent_at', 'last_sent_at']);
        });
    }
};
