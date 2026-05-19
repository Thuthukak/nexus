<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->string('net_terms')->default('custom')->after('due_date');
            $table->unsignedInteger('reminder_count')->default(0)->after('net_terms');
            $table->timestamp('last_reminder_sent_at')->nullable()->after('reminder_count');
        });
    }

    public function down(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->dropColumn(['net_terms', 'reminder_count', 'last_reminder_sent_at']);
        });
    }
};
