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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade'); // الوحدة
            $table->foreignId('expense_share_id')->constrained()->onDelete('cascade'); // الحصة من المصروف
            $table->decimal('amount', 10, 2); // المبلغ المدفوع
            $table->date('payment_date'); // تاريخ الدفع
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'online'])->default('cash'); // طريقة الدفع
            $table->string('reference_number')->nullable(); // رقم مرجعي
            $table->text('notes')->nullable(); // ملاحظات
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade'); // من سجل الدفع
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
