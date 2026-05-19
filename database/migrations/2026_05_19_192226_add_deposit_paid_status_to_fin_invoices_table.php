<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires modifying the enum column
        DB::statement("ALTER TABLE fin_invoices MODIFY COLUMN status ENUM(
            'draft','approved','sent','deposit_paid','part_paid','paid','overdue','cancelled'
        ) DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE fin_invoices MODIFY COLUMN status ENUM(
            'draft','approved','sent','part_paid','paid','overdue','cancelled'
        ) DEFAULT 'draft'");
    }
};
