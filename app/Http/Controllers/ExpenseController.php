<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Appointment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseController extends Controller
{
    public function exportPDF(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $expensesQuery = Expense::query();
        $appointmentsQuery = Appointment::where('status', 'completed');
        $salesQuery = Sale::query();

        if ($startDate || $endDate) {
            $start = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : null;
            $end = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : null;

            if ($start && $end) {
                $expensesQuery->whereBetween('date', [$start, $end]);
                $appointmentsQuery->whereBetween('appointment_date', [$start, $end]);
                $salesQuery->whereBetween('created_at', [$start, $end]);
            } elseif ($start) {
                $expensesQuery->where('date', '>=', $start);
                $appointmentsQuery->where('appointment_date', '>=', $start);
                $salesQuery->where('created_at', '>=', $start);
            } elseif ($end) {
                $expensesQuery->where('date', '<=', $end);
                $appointmentsQuery->where('appointment_date', '<=', $end);
                $salesQuery->where('created_at', '<=', $end);
            }
        }

        $expenses = $expensesQuery->latest()->get();
        $totalExpenses = $expenses->sum('amount');
        $expenseCash = $expenses->sum('cash_amount');
        $expenseTransfer = $expenses->sum('transfer_amount');

        // Financial Indicators (Appointments + Sales)
        $completedAppointments = $appointmentsQuery->with('service', 'professional', 'products')->get();
        $appointmentsRevenue = $completedAppointments->sum('grand_total');
        $appointmentsRevenueCash = $completedAppointments->sum('cash_amount');
        $appointmentsRevenueTransfer = $completedAppointments->sum('transfer_amount');

        $salesFetch = $salesQuery->with('items.product')->get();
        
        $grossRevenue = $appointmentsRevenue + $salesFetch->sum('total');
        $grossRevenueCash = $appointmentsRevenueCash + $salesFetch->where('payment_method', 'cash')->sum('total') + $salesFetch->where('payment_method', 'hybrid')->sum('cash_amount');
        $grossRevenueTransfer = $appointmentsRevenueTransfer + $salesFetch->where('payment_method', 'transfer')->sum('total') + $salesFetch->where('payment_method', 'hybrid')->sum('transfer_amount');
        
        $netProfit = $grossRevenue - $totalExpenses;
        
        // Net Balances by Source
        $netCash = $grossRevenueCash - $expenseCash;
        $netTransfer = $grossRevenueTransfer - $expenseTransfer;

        // Calculate period days (normalized to start of day)
        $realStart = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : \Carbon\Carbon::parse(Appointment::min('appointment_date') ?? now())->startOfDay();
        $realEnd = $endDate ? \Carbon\Carbon::parse($endDate)->startOfDay() : now()->startOfDay();
        $daysCount = (int) $realStart->diffInDays($realEnd) + 1; // +1 to include both ends

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.expenses.financial_report', compact(
            'expenses', 
            'completedAppointments',
            'salesFetch',
            'grossRevenue', 
            'grossRevenueCash',
            'grossRevenueTransfer',
            'totalExpenses', 
            'expenseCash',
            'expenseTransfer',
            'netProfit', 
            'netCash',
            'netTransfer',
            'startDate', 
            'endDate',
            'daysCount'
        ));

        $filename = 'reporte_financiero_' . ($startDate ?? 'inicio') . '_' . ($endDate ?? now()->format('Y-m-d')) . '.pdf';

        return $pdf->stream($filename);
    }

    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $expensesQuery = Expense::query();
        $appointmentsQuery = Appointment::where('status', 'completed');
        $salesQuery = Sale::query();

        if ($startDate || $endDate) {
            $start = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : null;
            $end = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : null;

            if ($start && $end) {
                $expensesQuery->whereBetween('date', [$start, $end]);
                $appointmentsQuery->whereBetween('appointment_date', [$start, $end]);
                $salesQuery->whereBetween('created_at', [$start, $end]);
            } elseif ($start) {
                $expensesQuery->where('date', '>=', $start);
                $appointmentsQuery->where('appointment_date', '>=', $start);
                $salesQuery->where('created_at', '>=', $start);
            } elseif ($end) {
                $expensesQuery->where('date', '<=', $end);
                $appointmentsQuery->where('appointment_date', '<=', $end);
                $salesQuery->where('created_at', '<=', $end);
            }
        }

        $expenses = $expensesQuery->latest()->paginate(50)->withQueryString();

        // Financial Indicators (Appointments + Sales)
        $completedAppointments = $appointmentsQuery->with('products')->get();
        $appointmentsRevenue = $completedAppointments->sum('grand_total');
        $appointmentsRevenueCash = $completedAppointments->sum('cash_amount');
        $appointmentsRevenueTransfer = $completedAppointments->sum('transfer_amount');

        $salesFetch = $salesQuery->get();
        $grossRevenue = $appointmentsRevenue + $salesFetch->sum('total');
        $grossRevenueCash = $appointmentsRevenueCash + $salesFetch->where('payment_method', 'cash')->sum('total') + $salesFetch->where('payment_method', 'hybrid')->sum('cash_amount');
        $grossRevenueTransfer = $appointmentsRevenueTransfer + $salesFetch->where('payment_method', 'transfer')->sum('total') + $salesFetch->where('payment_method', 'hybrid')->sum('transfer_amount');
        
        $allExpensesInPeriod = (clone $expensesQuery)->get();
        $totalExpenses = $allExpensesInPeriod->sum('amount');
        $expenseCash = $allExpensesInPeriod->sum('cash_amount');
        $expenseTransfer = $allExpensesInPeriod->sum('transfer_amount');

        $netProfit = $grossRevenue - $totalExpenses;
        
        // Net Balances by Source
        $netCash = $grossRevenueCash - $expenseCash;
        $netTransfer = $grossRevenueTransfer - $expenseTransfer;
        
        $projectedQuery = Appointment::where('status', 'confirmed');
        if ($startDate) { $projectedQuery->whereDate('appointment_date', '>=', $startDate); }
        if ($endDate) { $projectedQuery->whereDate('appointment_date', '<=', $endDate); }
        $projectedRevenue = (clone $projectedQuery)->get()->sum('final_price');

        return view('admin.expenses.index', compact(
            'expenses', 
            'grossRevenue', 
            'grossRevenueCash',
            'grossRevenueTransfer',
            'totalExpenses', 
            'expenseCash',
            'expenseTransfer',
            'netProfit', 
            'netCash',
            'netTransfer',
            'projectedRevenue',
            'startDate',
            'endDate'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,hybrid',
            'cash_amount' => 'nullable|numeric|min:0',
            'transfer_amount' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();
        
        // Ensure values for simple methods
        if ($data['payment_method'] === 'cash') {
            $data['cash_amount'] = $data['amount'];
            $data['transfer_amount'] = 0;
        } elseif ($data['payment_method'] === 'transfer') {
            $data['cash_amount'] = 0;
            $data['transfer_amount'] = $data['amount'];
        } else {
            // Hybrid: amounts must match total
            $sum = ($data['cash_amount'] ?? 0) + ($data['transfer_amount'] ?? 0);
            if (abs($sum - $data['amount']) > 1) {
                return back()->with('error', 'En el pago mixto, la suma de Caja ($' . number_format($data['cash_amount'], 0) . ') y Cuenta ($' . number_format($data['transfer_amount'], 0) . ') debe ser igual al Total ($' . number_format($data['amount'], 0) . ')');
            }
        }

        Expense::create($data);

        return back()->with('success', 'Gasto registrado correctamente.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Gasto eliminado.');
    }
}
