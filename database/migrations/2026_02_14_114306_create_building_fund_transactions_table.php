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
        Schema::create('building_fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('transaction_type', ['income', 'expense']);

            // مصدر الدخل
            $table->enum('source_type', ['monthly_due', 'direct_payment', 'subscription_payment'])->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            // نوع المصروف
            $table->enum('expense_type', ['subscription', 'maintenance', 'other'])->nullable();
            $table->unsignedBigInteger('expense_id')->nullable();

            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'transaction_type']);
            $table->index(['source_type', 'source_id']);
            $table->index(['expense_type', 'expense_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_fund_transactions');
    }
};
