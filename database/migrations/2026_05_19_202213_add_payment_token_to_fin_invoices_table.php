<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->string('payment_token', 64)->nullable()->unique()->after('id');
            $table->timestamp('payment_token_expires_at')->nullable()->after('payment_token');
        });
    }

    public function down(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_token', 'payment_token_expires_at']);
        });
    }
};
