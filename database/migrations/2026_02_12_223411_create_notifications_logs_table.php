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
        Schema::create('notifications_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // المستلم
            $table->enum('type', ['arrears', 'system', 'subscription', 'payment', 'expense'])->default('system'); // نوع الإشعار
            $table->string('title'); // عنوان الإشعار
            $table->text('message'); // نص الإشعار
            $table->timestamp('sent_at'); // وقت الإرسال
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null'); // من أرسل الإشعار
            $table->boolean('is_read')->default(false); // هل تم قراءته
            $table->timestamp('read_at')->nullable(); // وقت القراءة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_logs');
    }
};
