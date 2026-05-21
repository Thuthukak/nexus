<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            // deposit_type: 'percentage' or 'fixed'
            $table->string('deposit_type')->default('percentage')->after('deposit_required');
        });
    }

    public function down(): void
    {
        Schema::table('fin_invoices', function (Blueprint $table) {
            $table->dropColumn('deposit_type');
        });
    }
};
