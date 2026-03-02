<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Expense;
use App\Models\ExpenseShare;
use App\Models\NotificationLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    /**
     * عرض صفحة تسجيل المدفوعات
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ExpenseShare::with(['expense', 'apartment'])
                ->where('paid', false)
                ->orWhere('paid_amount', '<', \DB::raw('share_amount'));

            // فلتر حسب الشهر والسنة
            if ($request->filled('month')) {
                $query->whereHas('expense', function ($q) use ($request) {
                    $q->where('month', $request->month);
                });
            }

            if ($request->filled('year')) {
                $query->whereHas('expense', function ($q) use ($request) {
                    $q->where('year', $request->year);
                });
            }

            // فلتر حسب الوحدة
            if ($request->filled('apartment_id')) {
                $query->where('apartment_id', $request->apartment_id);
            }

            // فلتر حسب نوع المصروف
            if ($request->filled('expense_type')) {
                $query->whereHas('expense', function ($q) use ($request) {
                    $q->where('type', $request->expense_type);
                });
            }

            return DataTables::of($query)
                ->addColumn('apartment_number', function ($share) {
                    return $share->apartment->number ?? '-';
                })
                ->addColumn('expense_title', function ($share) {
                    return $share->expense->title ?? '-';
                })
                ->addColumn('expense_month', function ($share) {
                    return $share->expense->month_name ?? '-';
                })
                ->addColumn('share_amount', function ($share) {
                    return number_format($share->share_amount, 2);
                })
                ->addColumn('paid_amount', function ($share) {
                    return number_format($share->paid_amount ?? 0, 2);
                })
                ->addColumn('remaining_amount', function ($share) {
                    return number_format($share->remaining_amount, 2);
                })
                ->addColumn('status_label', function ($share) {
                    $status = $share->payment_status;
                    $labels = [
                        'paid' => '<span class="badge bg-success">مدفوع</span>',
                        'partial' => '<span class="badge bg-warning">مدفوع جزئياً</span>',
                        'unpaid' => '<span class="badge bg-danger">غير مدفوع</span>',
                    ];
                    return $labels[$status] ?? '';
                })
                ->addColumn('action', function ($share) {
                    if ($share->remaining_amount > 0) {
                        return '<button class="btn btn-sm btn-primary record-payment-btn"
                                data-id="' . $share->id . '"
                                data-apartment="' . $share->apartment->number . '"
                                data-expense="' . $share->expense->title . '"
                                data-remaining="' . $share->remaining_amount . '">
                            <i class="fas fa-money-bill"></i> تسجيل دفعة
                        </button>';
                    }
                    return '<span class="badge bg-success">مكتمل</span>';
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        }

        $apartments = Apartment::select('id', 'number')->orderBy('number')->get();
        $currentYear = now()->year;
        $currentMonth = now()->month;

        return view('payments.index', compact('apartments', 'currentYear', 'currentMonth'));
    }

    /**
     * تسجيل دفعة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_share_id' => 'required|exists:expense_shares,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,online',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $expenseShare = ExpenseShare::with(['expense', 'apartment'])->findOrFail($validated['expense_share_id']);

        // التحقق من أن المبلغ لا يتجاوز المتبقي
        if ($validated['amount'] > $expenseShare->remaining_amount) {
            return response()->json([
                'success' => false,
                'message' => 'المبلغ المدخل أكبر من المبلغ المتبقي'
            ], 422);
        }

        // تسجيل الدفعة باستخدام الـ method في ExpenseShare
        $payment = $expenseShare->recordPayment(
            $validated['amount'],
            auth()->id(),
            [
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // إرسال إشعار للمقيم
        if ($expenseShare->apartment->resident) {
            NotificationLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => $expenseShare->apartment->resident->id,
                'type' => 'payment',
                'title' => 'تم تسجيل دفعة جديدة',
                'message' => "تم تسجيل دفعة بمبلغ {$validated['amount']} ج.م لـ {$expenseShare->expense->title}",
                'sent_at' => now(),
                'sent_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدفعة بنجاح',
            'payment' => $payment
        ]);
    }

    /**
     * عرض سجل المدفوعات
     */
    public function history(Request $request)
    {
        if ($request->ajax()) {
            $query = Payment::with(['apartment', 'expenseShare.expense', 'recordedBy']);

            // فلتر حسب الوحدة
            if ($request->filled('apartment_id')) {
                $query->where('apartment_id', $request->apartment_id);
            }

            // فلتر حسب الفترة الزمنية
            if ($request->filled('date_from')) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addColumn('apartment_number', function ($payment) {
                    return $payment->apartment->number ?? '-';
                })
                ->addColumn('expense_title', function ($payment) {
                    return $payment->expenseShare->expense->title ?? '-';
                })
                ->addColumn('amount', function ($payment) {
                    return number_format($payment->amount, 2) . ' ج.م';
                })
                ->addColumn('payment_date', function ($payment) {
                    return $payment->payment_date->format('Y-m-d');
                })
                ->addColumn('payment_method', function ($payment) {
                    $methods = [
                        'cash' => 'نقدي',
                        'bank_transfer' => 'تحويل بنكي',
                        'check' => 'شيك',
                        'online' => 'إلكتروني',
                    ];
                    return $methods[$payment->payment_method] ?? '-';
                })
                ->addColumn('recorded_by', function ($payment) {
                    return $payment->recordedBy->name ?? '-';
                })
                ->make(true);
        }

        $apartments = Apartment::select('id', 'number')->orderBy('number')->get();

        return view('payments.history', compact('apartments'));
    }

    /**
     * حذف دفعة (في حالة الخطأ)
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $expenseShare = $payment->expenseShare;

        // تحديث المبلغ المدفوع في الحصة
        $expenseShare->paid_amount -= $payment->amount;

        // تحديث حالة الدفع
        if ($expenseShare->paid_amount < $expenseShare->share_amount) {
            $expenseShare->paid = false;
            $expenseShare->paid_at = null;
        }

        $expenseShare->save();

        // حذف الدفعة
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الدفعة بنجاح'
        ]);
    }
}
