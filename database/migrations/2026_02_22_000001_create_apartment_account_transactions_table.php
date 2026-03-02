<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartment_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade');
            $table->enum('transaction_type', ['credit', 'debit']);
            $table->string('source_type')->nullable(); // deposit | auto_expense_payment | auto_subscription_payment
            $table->unsignedBigInteger('source_id')->nullable(); // expense_share_id or subscription_id
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['apartment_id', 'transaction_type'], 'apt_acc_tx_apt_type_idx');
            $table->index(['tenant_id', 'apartment_id'], 'apt_acc_tx_tenant_apt_idx');
            $table->index('created_at', 'apt_acc_tx_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_account_transactions');
    }
};
