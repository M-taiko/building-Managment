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
            $table->enum('distribution_type', ['all', 'specific'])->default('all')->after('amount'); // نوع التوزيع: الكل أو شقة محددة
            $table->foreignId('apartment_id')->nullable()->after('distribution_type')->constrained()->onDelete('cascade'); // الشقة المحددة في حالة specific
            $table->boolean('is_one_time')->default(true)->after('apartment_id'); // مصروف لمرة واحدة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['distribution_type', 'apartment_id', 'is_one_time']);
        });
    }
};
