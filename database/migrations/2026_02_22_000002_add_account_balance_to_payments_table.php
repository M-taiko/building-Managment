<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method
            ENUM('cash','bank_transfer','check','online','account_balance') DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method
            ENUM('cash','bank_transfer','check','online') DEFAULT 'cash'");
    }
};
