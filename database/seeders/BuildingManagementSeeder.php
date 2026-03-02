<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BuildingManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin User (System Owner)
        \App\Models\User::create([
            'name' => 'مدير النظام',
            'email' => 'superadmin@masarsoft.io',
            'phone' => '0500000000',
            'password' => bcrypt('admin123'),
            'role' => 'super_admin',
        ]);

        // Create Tenant
        $tenant = \App\Models\Tenant::create([
            'tenant_code' => 'E001',
            'name' => 'عمارة النخيل',
            'address' => 'الرياض، حي الملقا',
            'subscription_price' => 500.00,
            'admin_name' => 'أحمد محمد العلي',
            'admin_email' => 'admin@building.com',
            'admin_phone' => '0501234567',
        ]);

        // Create Building Admin User
        $admin = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name' => 'أحمد محمد العلي',
            'email' => 'admin@building.com',
            'phone' => '0501234567',
            'password' => bcrypt('password'),
            'role' => 'building_admin',
        ]);

        // Create Apartments
        $apartments = [];
        for ($floor = 1; $floor <= 5; $floor++) {
            for ($unit = 1; $unit <= 4; $unit++) {
                $number = $floor . '0' . $unit;
                $apartments[] = \App\Models\Apartment::create([
                    'tenant_id' => $tenant->id,
                    'number' => $number,
                    'floor' => $floor === 1 ? 'الأرضي' : ($floor === 2 ? 'الأول' : ($floor === 3 ? 'الثاني' : ($floor === 4 ? 'الثالث' : 'الرابع'))),
                    'owner_name' => 'مالك الشقة ' . $number,
                ]);
            }
        }

        // Create Resident Users for first 3 apartments
        foreach (array_slice($apartments, 0, 3) as $index => $apartment) {
            \App\Models\User::create([
                'tenant_id' => $tenant->id,
                'apartment_id' => $apartment->id,
                'name' => 'مقيم الشقة ' . $apartment->number,
                'email' => 'resident' . ($index + 1) . '@building.com',
                'phone' => '050' . rand(1000000, 9999999),
                'password' => bcrypt('password'),
                'role' => 'resident',
            ]);
        }

        // Create Subscriptions for current year
        $currentYear = date('Y');
        foreach ($apartments as $apartment) {
            for ($month = 1; $month <= 12; $month++) {
                $paidAmount = $month < 6 ? 500 : ($month < 9 ? rand(0, 500) : 0);
                $status = $paidAmount >= 500 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

                \App\Models\Subscription::create([
                    'tenant_id' => $tenant->id,
                    'apartment_id' => $apartment->id,
                    'year' => $currentYear,
                    'month' => $month,
                    'amount' => 500.00,
                    'paid_amount' => $paidAmount,
                    'status' => $status,
                ]);
            }
        }

        // Create Expenses
        $expenseTypes = [
            ['title' => 'صيانة المصعد', 'amount' => 2500],
            ['title' => 'كهرباء المناطق المشتركة', 'amount' => 1800],
            ['title' => 'راتب عامل النظافة', 'amount' => 3000],
            ['title' => 'صيانة الحديقة', 'amount' => 1200],
            ['title' => 'مياه المناطق المشتركة', 'amount' => 800],
        ];

        foreach ($expenseTypes as $expense) {
            \App\Models\Expense::create([
                'tenant_id' => $tenant->id,
                'title' => $expense['title'],
                'description' => 'تفاصيل ' . $expense['title'],
                'amount' => $expense['amount'],
                'date' => now()->subDays(rand(1, 30)),
                'created_by' => $admin->id,
            ]);
        }

        // Create Maintenance Requests
        $maintenanceTypes = [
            ['title' => 'تسريب في الحمام', 'priority' => 'high', 'status' => 'in_progress'],
            ['title' => 'عطل في مكيف الهواء', 'priority' => 'medium', 'status' => 'open'],
            ['title' => 'باب المدخل يحتاج صيانة', 'priority' => 'low', 'status' => 'completed'],
            ['title' => 'شباك مكسور', 'priority' => 'medium', 'status' => 'open'],
        ];

        foreach ($maintenanceTypes as $index => $maintenance) {
            $apartment = $apartments[$index];
            \App\Models\MaintenanceRequest::create([
                'tenant_id' => $tenant->id,
                'apartment_id' => $apartment->id,
                'title' => $maintenance['title'],
                'description' => 'وصف مفصل لـ ' . $maintenance['title'],
                'priority' => $maintenance['priority'],
                'status' => $maintenance['status'],
                'created_by' => $admin->id,
            ]);
        }

        $this->command->info('تم إنشاء البيانات التجريبية بنجاح!');
        $this->command->info('');
        $this->command->info('=== بيانات مدير النظام (Super Admin) ===');
        $this->command->info('البريد الإلكتروني: superadmin@masarsoft.io');
        $this->command->info('كلمة المرور: admin123');
        $this->command->info('');
        $this->command->info('=== بيانات رئيس اتحاد الملاك (Building Admin) ===');
        $this->command->info('رقم العمارة: E001');
        $this->command->info('البريد الإلكتروني: admin@building.com');
        $this->command->info('كلمة المرور: password');
        $this->command->info('');
        $this->command->info('=== بيانات المقيم (Resident) ===');
        $this->command->info('رقم العمارة: E001');
        $this->command->info('البريد الإلكتروني: resident1@building.com');
        $this->command->info('كلمة المرور: password');
    }
}
