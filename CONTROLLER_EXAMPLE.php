<?php
/**
 * Example Controllers for Tenants and Users Management
 *
 * Copy these structures to your actual Controller files
 * Located in: app/Http/Controllers/
 */

// ==========================================
// TenantController Example
// ==========================================

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenantController extends Controller
{
    /**
     * Display a listing of the tenants.
     */
    public function index()
    {
        return view('tenants.index');
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        $presidents = User::where('role', 'union_president')->get();
        return view('tenants.create', compact('presidents'));
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tenants',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string',
            'president_id' => 'required|exists:users,id',
            'units_count' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive,pending',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('tenants', 'public');
        }

        Tenant::create($validated);

        return redirect()->route('tenants.index')
                        ->with('success', 'تم إضافة العمارة بنجاح');
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        $presidents = User::where('role', 'union_president')->get();
        return view('tenants.edit', compact('tenant', 'presidents'));
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tenants,name,' . $tenant->id,
            'city' => 'required|string|max:255',
            'address' => 'nullable|string',
            'president_id' => 'required|exists:users,id',
            'units_count' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive,pending',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($tenant->logo) {
                \Storage::disk('public')->delete($tenant->logo);
            }
            $validated['logo'] = $request->file('logo')->store('tenants', 'public');
        }

        $tenant->update($validated);

        return redirect()->route('tenants.index')
                        ->with('success', 'تم تحديث العمارة بنجاح');
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(Tenant $tenant)
    {
        // Delete logo if exists
        if ($tenant->logo) {
            \Storage::disk('public')->delete($tenant->logo);
        }

        $tenant->delete();

        return redirect()->route('tenants.index')
                        ->with('success', 'تم حذف العمارة بنجاح');
    }

    /**
     * Get tenants data for DataTables (AJAX)
     */
    public function apiIndex(Request $request)
    {
        if ($request->ajax()) {
            $query = Tenant::query()
                ->with('president:id,name')
                ->select([
                    'id',
                    'name',
                    'city',
                    'president_id',
                    'units_count',
                    'status',
                    'created_at'
                ]);

            return DataTables::of($query)
                ->addColumn('president_name', function ($row) {
                    return $row->president?->name ?? '-';
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn-action btn-view" onclick="viewTenant(' . $row->id . ')"><i class="fas fa-eye"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Get presidents list (for dropdown)
     */
    public function getPresidents()
    {
        return User::where('role', 'union_president')
                   ->select('id', 'name')
                   ->get();
    }

    /**
     * Show tenant details (AJAX)
     */
    public function show(Tenant $tenant)
    {
        return response()->json($tenant->with('president:id,name')->first());
    }
}

// ==========================================
// UserController Example
// ==========================================

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        return view('users.index');
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $tenants = Tenant::select('id', 'name')->get();
        return view('users.create', compact('tenants'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|max:20',
            'tenant_id' => 'required|exists:tenants,id',
            'unit_number' => 'required|string|max:50',
            'user_type' => 'required|in:resident,owner,tenant',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,inactive,pending',
            'is_admin' => 'nullable|boolean'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_admin'] = $request->has('is_admin');

        User::create($validated);

        return redirect()->route('users.index')
                        ->with('success', 'تم إضافة المستخدم بنجاح');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $tenants = Tenant::select('id', 'name')->get();
        return view('users.edit', compact('user', 'tenants'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'tenant_id' => 'required|exists:tenants,id',
            'unit_number' => 'required|string|max:50',
            'user_type' => 'required|in:resident,owner,tenant',
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|in:active,inactive,pending',
            'is_admin' => 'nullable|boolean'
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_admin'] = $request->has('is_admin');

        $user->update($validated);

        return redirect()->route('users.index')
                        ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting the logged-in user
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                            ->with('error', 'لا يمكن حذف حسابك الخاص');
        }

        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * Get users data for DataTables (AJAX)
     */
    public function apiIndex(Request $request)
    {
        if ($request->ajax()) {
            $query = User::query()
                ->with('tenant:id,name')
                ->select([
                    'id',
                    'name',
                    'email',
                    'phone',
                    'tenant_id',
                    'unit_number',
                    'status',
                    'created_at'
                ]);

            return DataTables::of($query)
                ->addColumn('tenant_name', function ($row) {
                    return $row->tenant?->name ?? '-';
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn-action btn-view" onclick="viewUser(' . $row->id . ')"><i class="fas fa-eye"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Get tenants list (for dropdown)
     */
    public function getTenants()
    {
        return Tenant::select('id', 'name')
                     ->where('status', 'active')
                     ->get();
    }

    /**
     * Show user details (AJAX)
     */
    public function show(User $user)
    {
        return response()->json(
            $user->with('tenant:id,name')->first()
        );
    }
}

/**
 * ==========================================
 * Model Relationships
 * ==========================================
 *
 * // Tenant Model
 * public function president()
 * {
 *     return $this->belongsTo(User::class, 'president_id');
 * }
 *
 * public function users()
 * {
 *     return $this->hasMany(User::class);
 * }
 *
 * // User Model
 * public function tenant()
 * {
 *     return $this->belongsTo(Tenant::class);
 * }
 */
