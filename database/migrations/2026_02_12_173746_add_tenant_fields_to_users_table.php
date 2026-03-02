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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('apartment_id')->after('tenant_id')->nullable()->constrained()->onDelete('set null');
            $table->string('phone')->after('email')->nullable();
            $table->enum('role', ['super_admin', 'building_admin', 'resident'])->after('phone')->default('resident');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['apartment_id']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'apartment_id', 'phone', 'role']);
        });
    }
};
