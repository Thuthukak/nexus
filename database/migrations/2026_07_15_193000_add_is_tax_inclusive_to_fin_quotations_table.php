<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fin_quotation_lines', function (Blueprint $table) {
            $table->boolean('is_tax_inclusive')->default(true)->after('tax_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fin_quotations', function (Blueprint $table) {
            $table->dropColumn('is_tax_inclusive');
        });
    }
};
