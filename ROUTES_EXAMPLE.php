<?php
/**
 * Routes Example for Tenants and Users Management
 *
 * Add these routes to your routes/web.php file
 */

// ==========================================
// Super Admin Routes - Tenants Management
// ==========================================
Route::middleware(['auth', 'super_admin'])->group(function () {

    // Web Routes for Tenants
    Route::prefix('admin')->group(function () {
        // Tenant CRUD
        Route::resource('tenants', 'App\Http\Controllers\TenantController');

        // Tenant API Endpoints
        Route::get('api/tenants', 'App\Http\Controllers\TenantController@apiIndex')->name('api.tenants');
        Route::get('api/tenants/{id}', 'App\Http\Controllers\TenantController@show')->name('api.tenants.show');
        Route::post('api/tenants', 'App\Http\Controllers\TenantController@store')->name('api.tenants.store');
        Route::put('api/tenants/{id}', 'App\Http\Controllers\TenantController@update')->name('api.tenants.update');
        Route::delete('api/tenants/{id}', 'App\Http\Controllers\TenantController@destroy')->name('api.tenants.destroy');

        // Helper API Endpoints
        Route::get('api/presidents', 'App\Http\Controllers\TenantController@getPresidents')->name('api.presidents');
    });
});

// ==========================================
// Union President Routes - Users Management
// ==========================================
Route::middleware(['auth', 'union_president'])->group(function () {

    // Web Routes for Users
    Route::prefix('admin')->group(function () {
        // User CRUD
        Route::resource('users', 'App\Http\Controllers\UserController');

        // User API Endpoints
        Route::get('api/users', 'App\Http\Controllers\UserController@apiIndex')->name('api.users');
        Route::get('api/users/{id}', 'App\Http\Controllers\UserController@show')->name('api.users.show');
        Route::post('api/users', 'App\Http\Controllers\UserController@store')->name('api.users.store');
        Route::put('api/users/{id}', 'App\Http\Controllers\UserController@update')->name('api.users.update');
        Route::delete('api/users/{id}', 'App\Http\Controllers\UserController@destroy')->name('api.users.destroy');

        // Helper API Endpoints
        Route::get('api/tenants/list', 'App\Http\Controllers\UserController@getTenants')->name('api.tenants.list');
    });
});

/**
 * Expected Controller Methods
 *
 * === TenantController ===
 *
 * // Web Methods
 * public function index()
 * public function create()
 * public function store(TenantRequest $request)
 * public function show(Tenant $tenant)
 * public function edit(Tenant $tenant)
 * public function update(TenantRequest $request, Tenant $tenant)
 * public function destroy(Tenant $tenant)
 *
 * // API Methods
 * public function apiIndex(Request $request)
 * public function getPresidents()
 *
 *
 * === UserController ===
 *
 * // Web Methods
 * public function index()
 * public function create()
 * public function store(UserRequest $request)
 * public function show(User $user)
 * public function edit(User $user)
 * public function update(UserRequest $request, User $user)
 * public function destroy(User $user)
 *
 * // API Methods
 * public function apiIndex(Request $request)
 * public function getTenants()
 */

/**
 * Alternative route grouping (if you prefer inline definitions)
 *
 * // Tenants Routes
 * Route::post('admin/api/tenants', [TenantController::class, 'store'])->name('api.tenants.store');
 * Route::put('admin/api/tenants/{id}', [TenantController::class, 'update'])->name('api.tenants.update');
 * Route::delete('admin/api/tenants/{id}', [TenantController::class, 'destroy'])->name('api.tenants.destroy');
 * Route::get('admin/api/tenants/{id}', [TenantController::class, 'show'])->name('api.tenants.show');
 *
 * // Users Routes
 * Route::post('admin/api/users', [UserController::class, 'store'])->name('api.users.store');
 * Route::put('admin/api/users/{id}', [UserController::class, 'update'])->name('api.users.update');
 * Route::delete('admin/api/users/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');
 * Route::get('admin/api/users/{id}', [UserController::class, 'show'])->name('api.users.show');
 */
