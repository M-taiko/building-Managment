<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateRecurringExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring expenses automatically (monthly/yearly)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to generate recurring expenses...');

        $today = now()->startOfDay();

        // الحصول على المصروفات المتكررة التي حان موعدها
        $recurringExpenses = Expense::where('is_recurring', true)
            ->whereIn('recurrence_type', ['monthly', 'yearly'])
            ->where(function($query) use ($today) {
                $query->whereNull('next_occurrence_date')
                    ->orWhere('next_occurrence_date', '<=', $today);
            })
            ->get();

        if ($recurringExpenses->isEmpty()) {
            $this->info('No recurring expenses to generate.');
            return 0;
        }

        $generatedCount = 0;

        foreach ($recurringExpenses as $originalExpense) {
            DB::beginTransaction();
            try {
                // إنشاء مصروف جديد مبني على المصروف الأصلي
                $newExpense = Expense::create([
                    'tenant_id' => $originalExpense->tenant_id,
                    'title' => $originalExpense->title,
                    'description' => $originalExpense->description,
                    'amount' => $originalExpense->amount,
                    'date' => now(),
                    'distribution_type' => $originalExpense->distribution_type,
                    'apartment_id' => $originalExpense->apartment_id,
                    'is_recurring' => false, // المصروف المولد ليس متكرر
                    'recurrence_type' => 'one_time',
                    'subscription_type_id' => $originalExpense->subscription_type_id,
                    'status' => 'pending',
                    'created_by' => $originalExpense->created_by,
                ]);

                // توزيع المصروف
                $distributed = $newExpense->distribute();

                if ($distributed) {
                    // إرسال إشعارات للسكان
                    foreach ($newExpense->shares as $share) {
                        if ($share->apartment->resident) {
                            NotificationLog::create([
                                'tenant_id' => $originalExpense->tenant_id,
                                'user_id' => $share->apartment->resident->id,
                                'notification_type' => 'building_expense',
                                'related_type' => 'Expense',
                                'related_id' => $newExpense->id,
                                'title' => 'مصروف متكرر: ' . $newExpense->title,
                                'message' => 'تم توزيع مصروف متكرر. حصتك: ' . number_format($share->share_amount, 2) . ' ج.م',
                                'sent_at' => now(),
                                'sent_by' => $originalExpense->created_by,
                            ]);
                        }
                    }
                }

                // تحديث تاريخ التكرار التالي
                $nextOccurrence = $originalExpense->recurrence_type === 'monthly'
                    ? now()->addMonth()
                    : now()->addYear();

                $originalExpense->update([
                    'next_occurrence_date' => $nextOccurrence,
                    'last_generated_date' => now(),
                ]);

                $generatedCount++;
                $this->info("Generated expense: {$newExpense->title}");

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to generate expense for: {$originalExpense->title}");
                $this->error($e->getMessage());
            }
        }

        $this->info("Successfully generated {$generatedCount} recurring expenses.");
        return 0;
    }
}
