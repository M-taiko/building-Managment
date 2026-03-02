<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    /**
     * عرض قائمة العمارات
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tenants = Tenant::select('tenants.*');

            return DataTables::of($tenants)
                ->addColumn('action', function ($tenant) {
                    return '
                        <button class="btn btn-sm btn-info edit-btn" data-id="' . $tenant->id . '">تعديل</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="' . $tenant->id . '">حذف</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('tenants.index');
    }

    /**
     * نموذج إضافة عمارة جديدة
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json(['html' => view('tenants.create')->render()]);
    }

    /**
     * حفظ عمارة جديدة وإنشاء مستخدم رئيس الاتحاد تلقائياً
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_code' => 'required|string|max:255|unique:tenants,tenant_code',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'subscription_price' => 'required|numeric|min:0',
            'units_count' => 'required|integer|min:1',
            'subscription_expires_at' => 'nullable|date',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_phone' => 'required|string|max:20',
            'admin_password' => 'required|string|min:6',
        ]);

        try {
            // 1. إنشاء العمارة (Tenant)
            $tenant = Tenant::create([
                'tenant_code' => $validated['tenant_code'],
                'name' => $validated['name'],
                'address' => $validated['address'],
                'subscription_price' => $validated['subscription_price'],
                'units_count' => $validated['units_count'],
                'subscription_expires_at' => $validated['subscription_expires_at'] ?? now()->addYear(),
                'subscription_status' => 'active',
                'admin_name' => $validated['admin_name'],
                'admin_email' => $validated['admin_email'],
                'admin_phone' => $validated['admin_phone'],
            ]);

            // 2. إنشاء مستخدم رئيس الاتحاد تلقائياً
            $user = User::create([
                'tenant_id' => $tenant->id,
                'apartment_id' => null,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'phone' => $validated['admin_phone'],
                'password' => Hash::make($validated['admin_password']),
                'role' => 'building_admin',
            ]);

            // 3. إسناد الرول للمستخدم
            $user->assignRole('building_admin');

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العمارة وحساب رئيس الاتحاد بنجاح',
                'tenant_id' => $tenant->id,
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض تفاصيل عمارة محددة
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * نموذج تعديل عمارة
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);
        return response()->json(['html' => view('tenants.edit', compact('tenant'))->render()]);
    }

    /**
     * تحديث بيانات العمارة
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'tenant_code' => 'required|string|max:255|unique:tenants,tenant_code,' . $id,
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'subscription_price' => 'required|numeric|min:0',
            'units_count' => 'required|integer|min:1',
            'subscription_expires_at' => 'nullable|date',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email,' . $tenant->id . ',id',
            'admin_phone' => 'required|string|max:20',
        ]);

        try {
            // تحديث بيانات العمارة
            $tenant->update([
                'tenant_code' => $validated['tenant_code'],
                'name' => $validated['name'],
                'address' => $validated['address'],
                'subscription_price' => $validated['subscription_price'],
                'units_count' => $validated['units_count'],
                'subscription_expires_at' => $validated['subscription_expires_at'],
                'admin_name' => $validated['admin_name'],
                'admin_email' => $validated['admin_email'],
                'admin_phone' => $validated['admin_phone'],
            ]);

            // تحديث بيانات المستخدم إذا كان موجوداً
            $user = User::where('tenant_id', $tenant->id)
                ->where('role', 'building_admin')
                ->first();

            if ($user) {
                $user->update([
                    'name' => $validated['admin_name'],
                    'email' => $validated['admin_email'],
                    'phone' => $validated['admin_phone'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات العمارة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف عمارة
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);

            // حذف جميع المستخدمين المرتبطين بالعمارة
            User::where('tenant_id', $tenant->id)->delete();

            // حذف العمارة
            $tenant->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العمارة وجميع البيانات المرتبطة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
