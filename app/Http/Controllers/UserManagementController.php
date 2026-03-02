<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    /**
     * عرض قائمة المقيمين في العمارة
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // الحصول على المستخدمين من نفس العمارة الخاصة بـ Building Admin
            $buildingAdmin = Auth::user();
            $users = User::where('tenant_id', $buildingAdmin->tenant_id)
                ->where('role', '!=', 'building_admin')
                ->with('apartment')
                ->select('users.*');

            return DataTables::of($users)
                ->addColumn('apartment_number', function ($user) {
                    return $user->apartment ? 'الشقة ' . $user->apartment->number : 'بدون شقة';
                })
                ->addColumn('action', function ($user) {
                    return '
                        <button class="btn btn-sm btn-info edit-btn" data-id="' . $user->id . '">تعديل</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="' . $user->id . '">حذف</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('users.index');
    }

    /**
     * نموذج إضافة مقيم جديد
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buildingAdmin = Auth::user();
        $apartments = Apartment::where('tenant_id', $buildingAdmin->tenant_id)->get();

        return response()->json([
            'html' => view('users.create', compact('apartments'))->render()
        ]);
    }

    /**
     * حفظ مقيم جديد
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $buildingAdmin = Auth::user();

        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        try {
            // التحقق من أن الشقة تنتمي إلى نفس العمارة
            $apartment = Apartment::findOrFail($validated['apartment_id']);
            if ($apartment->tenant_id !== $buildingAdmin->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذه الشقة لا تنتمي إلى عمارتك'
                ], 403);
            }

            // إنشاء المستخدم الجديد
            $user = User::create([
                'tenant_id' => $buildingAdmin->tenant_id,
                'apartment_id' => $validated['apartment_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'resident',
            ]);

            // إسناد الرول للمستخدم
            $user->assignRole('resident');

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المقيم بنجاح',
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
     * عرض تفاصيل مستخدم محدد
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * نموذج تعديل مقيم
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $buildingAdmin = Auth::user();
        $user = User::findOrFail($id);

        // التحقق من أن المستخدم ينتمي إلى نفس العمارة
        if ($user->tenant_id !== $buildingAdmin->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية للوصول إلى هذا المستخدم'
            ], 403);
        }

        $apartments = Apartment::where('tenant_id', $buildingAdmin->tenant_id)->get();

        return response()->json([
            'html' => view('users.edit', compact('user', 'apartments'))->render()
        ]);
    }

    /**
     * تحديث بيانات مقيم
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $buildingAdmin = Auth::user();
        $user = User::findOrFail($id);

        // التحقق من أن المستخدم ينتمي إلى نفس العمارة
        if ($user->tenant_id !== $buildingAdmin->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية لتعديل هذا المستخدم'
            ], 403);
        }

        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
        ]);

        try {
            // التحقق من أن الشقة تنتمي إلى نفس العمارة
            $apartment = Apartment::findOrFail($validated['apartment_id']);
            if ($apartment->tenant_id !== $buildingAdmin->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذه الشقة لا تنتمي إلى عمارتك'
                ], 403);
            }

            // تحديث بيانات المستخدم
            $updateData = [
                'apartment_id' => $validated['apartment_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ];

            // تحديث كلمة المرور إذا تم تقديم واحدة
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المقيم بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف مقيم
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $buildingAdmin = Auth::user();

        try {
            $user = User::findOrFail($id);

            // التحقق من أن المستخدم ينتمي إلى نفس العمارة
            if ($user->tenant_id !== $buildingAdmin->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية لحذف هذا المستخدم'
                ], 403);
            }

            // منع حذف building_admin
            if ($user->role === 'building_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف رئيس الاتحاد'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المقيم بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
