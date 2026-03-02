@extends('layouts.admin')

@section('page-title', 'لوحة التحكم - مدير النظام')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row">
        <!-- Total Tenants -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-right-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي العمارات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_tenants'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-right-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                العمارات النشطة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_tenants'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-right-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                الإيرادات الشهرية
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['monthly_revenue'], 2) }} ج.م
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yearly Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-right-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                الإيرادات السنوية المتوقعة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['yearly_revenue'], 2) }} ج.م
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenants Subscription Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">اشتراكات العمارات في البرنامج</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tenants-table" width="100%">
                            <thead>
                                <tr>
                                    <th>رقم العمارة</th>
                                    <th>اسم العمارة</th>
                                    <th>العنوان</th>
                                    <th>رئيس الاتحاد</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الهاتف</th>
                                    <th>سعر الاشتراك</th>
                                    <th>عدد الشقق</th>
                                    <th>عدد السكان</th>
                                    <th>تاريخ التسجيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['tenants'] as $tenant)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $tenant->tenant_code }}</span></td>
                                    <td>{{ $tenant->name }}</td>
                                    <td>{{ $tenant->address ?? '-' }}</td>
                                    <td>{{ $tenant->admin_name }}</td>
                                    <td>{{ $tenant->admin_email }}</td>
                                    <td>{{ $tenant->admin_phone }}</td>
                                    <td class="text-success font-weight-bold">{{ number_format($tenant->subscription_price, 2) }} ج.م</td>
                                    <td>{{ $tenant->apartments_count }}</td>
                                    <td>{{ $tenant->users_count }}</td>
                                    <td>{{ $tenant->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($('#tenants-table tbody tr').length > 0) {
        $('#tenants-table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
            },
            pageLength: 25,
            order: [[9, 'desc']], // Sort by created_at DESC
            columnDefs: [
                { targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], orderable: true }
            ],
            autoWidth: false,
            retrieve: true,
            destroy: true
        });
    }
});
</script>
@endpush
