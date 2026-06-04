<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_customers', function (Blueprint $table) {

            $table->boolean('portal_enabled')->default(false)->after('is_active');
            $table->timestamp('portal_invited_at')->nullable()->after('portal_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('fin_customers', function (Blueprint $table) {
            $table->dropColumn(['portal_enabled', 'portal_invited_at']);
        });
    }
};
