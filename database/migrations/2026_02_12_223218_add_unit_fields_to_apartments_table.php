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
        Schema::table('apartments', function (Blueprint $table) {
            $table->enum('type', ['residential', 'commercial'])->default('residential')->after('owner_name'); // نوع الوحدة
            $table->enum('share_type', ['equal', 'custom'])->default('equal')->after('type'); // نوع التوزيع
            $table->decimal('custom_share_percentage', 5, 2)->nullable()->after('share_type'); // نسبة التوزيع المخصصة
            $table->boolean('is_active')->default(true)->after('custom_share_percentage'); // حالة الوحدة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn(['type', 'share_type', 'custom_share_percentage', 'is_active']);
        });
    }
};
