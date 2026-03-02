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
            $table->boolean('is_recurring')->default(false)->after('amount');
            $table->enum('recurrence_type', ['one_time', 'monthly', 'yearly'])->default('one_time')->after('is_recurring');
            $table->foreignId('subscription_type_id')->nullable()->after('recurrence_type')->constrained()->onDelete('set null');
            $table->date('next_occurrence_date')->nullable()->after('subscription_type_id');
            $table->date('last_generated_date')->nullable()->after('next_occurrence_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['subscription_type_id']);
            $table->dropColumn([
                'is_recurring',
                'recurrence_type',
                'subscription_type_id',
                'next_occurrence_date',
                'last_generated_date'
            ]);
        });
    }
};
