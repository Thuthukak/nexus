<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('guard', ['internal', 'customer'])->default('internal')->after('email');
            $table->boolean('portal_access')->default(false)->after('guard');
            $table->json('notification_preferences')->nullable()->after('portal_access');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['guard', 'portal_access', 'notification_preferences']);
        });
    }
};