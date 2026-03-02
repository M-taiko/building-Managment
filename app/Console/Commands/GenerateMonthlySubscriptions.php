<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\Tenant;
use App\Http\Controllers\NotificationController;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateMonthlySubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:generate-monthly {--month= : Month to generate (default: current month)} {--year= : Year to generate (default: current year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly subscriptions for all apartments based on their assigned subscription types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month') ?? now()->month;
        $year = $this->option('year') ?? now()->year;

        $this->info("🚀 توليد الاشتراكات الشهرية لـ {$year}-{$month}...");
        $this->newLine();

        $tenants = Tenant::all();
        $totalGenerated = 0;
        $totalSkipped = 0;

        foreach ($tenants as $tenant) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏢 معالجة عمارة: {$tenant->name}");

            // Get all active apartments for this tenant
            $apartments = Apartment::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->get();

            if ($apartments->isEmpty()) {
                $this->warn("   ⚠️  لا توجد شقق نشطة");
                continue;
            }

            $this->line("   📊 عدد الشقق النشطة: {$apartments->count()}");
            $this->newLine();

            foreach ($apartments as $apartment) {
                $this->line("   🏠 الشقة رقم: {$apartment->number}");

                // Get active subscription types assigned to this apartment
                $subscriptionTypes = $apartment->activeSubscriptionTypes()->get();

                if ($subscriptionTypes->isEmpty()) {
                    $this->warn("      ⚠️  لا توجد اشتراكات مخصصة لهذه الشقة");
                    $this->newLine();
                    continue;
                }

                foreach ($subscriptionTypes as $type) {
                    // Check if subscription already exists
                    $exists = Subscription::where('tenant_id', $tenant->id)
                        ->where('apartment_id', $apartment->id)
                        ->where('subscription_type_id', $type->id)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->exists();

                    if ($exists) {
                        $this->line("      ⏭️  {$type->name} - موجود مسبقاً");
                        $totalSkipped++;
                        continue;
                    }

                    // Create new subscription
                    $subscription = Subscription::create([
                        'tenant_id' => $tenant->id,
                        'apartment_id' => $apartment->id,
                        'subscription_type_id' => $type->id,
                        'year' => $year,
                        'month' => $month,
                        'amount' => $type->amount,
                        'paid_amount' => 0,
                        'status' => 'pending',
                    ]);

                    $this->info("      ✅ {$type->name} - " . number_format($type->amount, 2) . " ج.م");

                    // Notify the resident
                    if ($apartment->resident) {
                        try {
                            NotificationController::notifyUser(
                                $apartment->resident->id,
                                'subscription',
                                $subscription->id,
                                'اشتراك جديد: ' . $type->name,
                                'تم إنشاء اشتراك ' . $type->name . ' لشهر ' . $month . '/' . $year . ' بقيمة ' . number_format($type->amount, 2) . ' ج.م'
                            );
                        } catch (\Exception $e) {
                            $this->warn("      ⚠️  فشل إرسال الإشعار: " . $e->getMessage());
                        }
                    }

                    $totalGenerated++;
                }

                $this->newLine();
            }
        }

        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();
        $this->info("📊 ملخص التوليد:");
        $this->line("   ✅ تم إنشاء: {$totalGenerated} اشتراك");
        $this->line("   ⏭️  تم التخطي: {$totalSkipped} اشتراك (موجود مسبقاً)");
        $this->newLine();

        if ($totalGenerated > 0) {
            $this->info("🎉 تم توليد الاشتراكات بنجاح!");
        } else {
            $this->comment("ℹ️  لم يتم إنشاء اشتراكات جديدة");
        }

        return 0;
    }
}
