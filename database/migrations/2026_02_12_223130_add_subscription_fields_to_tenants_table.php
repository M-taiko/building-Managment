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
        Schema::table('tenants', function (Blueprint $table) {
            $table->integer('units_count')->default(0)->after('subscription_price'); // عدد الوحدات المسموح بها
            $table->timestamp('subscription_expires_at')->nullable()->after('units_count'); // تاريخ انتهاء اشتراك البرنامج
            $table->enum('subscription_status', ['active', 'expired', 'trial'])->default('trial')->after('subscription_expires_at'); // حالة الاشتراك
            $table->timestamp('last_subscription_reminder')->nullable()->after('subscription_status'); // آخر تذكير بالاشتراك
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['units_count', 'subscription_expires_at', 'subscription_status', 'last_subscription_reminder']);
        });
    }
};
