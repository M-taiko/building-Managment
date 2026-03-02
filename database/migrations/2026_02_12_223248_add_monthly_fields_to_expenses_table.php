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
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('type', ['monthly', 'one_time'])->default('one_time')->after('description'); // نوع المصروف
            $table->integer('month')->nullable()->after('type'); // الشهر (للمصروفات الشهرية)
            $table->integer('year')->nullable()->after('month'); // السنة (للمصروفات الشهرية)
            $table->enum('status', ['pending', 'distributed'])->default('pending')->after('date'); // حالة التوزيع
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['type', 'month', 'year', 'status']);
        });
    }
};
