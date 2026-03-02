<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MaintenanceRequest::with(['apartment', 'creator'])->select('maintenance_requests.*');

            // Apply status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply priority filter
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            return DataTables::of($query)
                ->addColumn('apartment_number', function ($maintenanceRequest) {
                    return $maintenanceRequest->apartment ? $maintenanceRequest->apartment->number : '-';
                })
                ->addColumn('creator_name', function ($maintenanceRequest) {
                    return $maintenanceRequest->creator ? $maintenanceRequest->creator->name : '-';
                })
                ->addColumn('status_label', function ($maintenanceRequest) {
                    $statusLabels = [
                        'pending' => '<span class="badge bg-warning">قيد الانتظار</span>',
                        'in_progress' => '<span class="badge bg-info">قيد التنفيذ</span>',
                        'completed' => '<span class="badge bg-success">مكتمل</span>',
                        'cancelled' => '<span class="badge bg-danger">ملغي</span>',
                    ];
                    return $statusLabels[$maintenanceRequest->status] ?? '<span class="badge bg-secondary">غير محدد</span>';
                })
                ->addColumn('priority_label', function ($maintenanceRequest) {
                    $priorityLabels = [
                        'low' => '<span class="badge bg-secondary">منخفض</span>',
                        'medium' => '<span class="badge bg-primary">متوسط</span>',
                        'high' => '<span class="badge bg-warning">عالي</span>',
                        'urgent' => '<span class="badge bg-danger">عاجل</span>',
                    ];
                    return $priorityLabels[$maintenanceRequest->priority] ?? '<span class="badge bg-secondary">غير محدد</span>';
                })
                ->addColumn('action', function ($maintenanceRequest) {
                    return '
                        <button class="btn btn-sm btn-info edit-btn" data-id="' . $maintenanceRequest->id . '">تعديل</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="' . $maintenanceRequest->id . '">حذف</button>
                    ';
                })
                ->rawColumns(['status_label', 'priority_label', 'action'])
                ->make(true);
        }

        return view('maintenance_requests.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apartments = Apartment::all();
        return response()->json(['html' => view('maintenance_requests.create', compact('apartments'))->render()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $validated['created_by'] = Auth::id();

        $maintenance = MaintenanceRequest::create($validated);

        // Send notification to all residents
        $priorityText = [
            'low' => 'منخفضة',
            'medium' => 'متوسطة',
            'high' => 'عالية',
            'urgent' => 'عاجلة'
        ];

        NotificationController::notifyResidents(
            auth()->user()->tenant_id,
            'maintenance',
            $maintenance->id,
            'طلب صيانة جديد: ' . $maintenance->title,
            'تم إضافة طلب صيانة جديد بأولوية ' . ($priorityText[$maintenance->priority] ?? '') . '. ' . ($maintenance->description ?? '')
        );

        return response()->json(['success' => true, 'message' => 'تم إضافة طلب الصيانة بنجاح']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $apartments = Apartment::all();
        return response()->json(['html' => view('maintenance_requests.edit', compact('maintenanceRequest', 'apartments'))->render()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);

        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $oldStatus = $maintenanceRequest->status;
        $maintenanceRequest->update($validated);

        // Send notification if status changed to completed
        if ($oldStatus !== 'completed' && $validated['status'] === 'completed') {
            NotificationController::notifyResidents(
                auth()->user()->tenant_id,
                'maintenance',
                $maintenanceRequest->id,
                'تم إنجاز الصيانة: ' . $maintenanceRequest->title,
                'تم الانتهاء من صيانة: ' . $maintenanceRequest->title
            );
        }

        return response()->json(['success' => true, 'message' => 'تم تعديل طلب الصيانة بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $maintenanceRequest->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف طلب الصيانة بنجاح']);
    }
}
