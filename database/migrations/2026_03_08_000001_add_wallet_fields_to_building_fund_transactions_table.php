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
        Schema::table('building_fund_transactions', function (Blueprint $table) {
            $table->enum('wallet_type', ['general', 'cash', 'bank'])->default('general')->after('balance_after');
            $table->foreignId('custody_user_id')->nullable()->constrained('users')->nullOnDelete()->after('wallet_type');
            $table->text('custody_notes')->nullable()->after('custody_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_fund_transactions', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'custody_user_id');
            $table->dropColumn(['wallet_type', 'custody_user_id', 'custody_notes']);
        });
    }
};
