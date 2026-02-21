<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Appointment;
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

        if ($startDate) {
            $expensesQuery->where('date', '>=', $startDate);
            $appointmentsQuery->whereDate('appointment_date', '>=', $startDate);
        }
        if ($endDate) {
            $expensesQuery->where('date', '<=', $endDate);
            $appointmentsQuery->whereDate('appointment_date', '<=', $endDate);
        }

        $expenses = $expensesQuery->latest()->get();

        // Financial Indicators
        $completedAppointments = (clone $appointmentsQuery)->with('service', 'professional')->get();
        $grossRevenue = $completedAppointments->sum('final_price');
        $totalExpenses = (clone $expensesQuery)->sum('amount');
        $netProfit = $grossRevenue - $totalExpenses;


        // Usamos el contenedor de servicios para evitar problemas si la fachada no se descubre correctamente
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.expenses.financial_report', compact(
            'expenses', 
            'completedAppointments',
            'grossRevenue', 
            'totalExpenses', 
            'netProfit', 
            'startDate', 
            'endDate'
        ));



        $filename = 'reporte_financiero_' . ($startDate ?? 'inicio') . '_' . ($endDate ?? now()->format('Y-m-d')) . '.pdf';

        return $pdf->download($filename);
    }

    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $expensesQuery = Expense::query();
        $appointmentsQuery = Appointment::where('status', 'completed');

        if ($startDate) {
            $expensesQuery->where('date', '>=', $startDate);
            $appointmentsQuery->whereDate('appointment_date', '>=', $startDate);
        }
        if ($endDate) {
            $expensesQuery->where('date', '<=', $endDate);
            $appointmentsQuery->whereDate('appointment_date', '<=', $endDate);
        }

        $expenses = $expensesQuery->latest()->paginate(50)->withQueryString();

        // Financial Indicators
        $grossRevenue = $appointmentsQuery->get()->sum('final_price');
        $totalExpenses = (clone $expensesQuery)->sum('amount');
        $netProfit = $grossRevenue - $totalExpenses;
        
        $projectedQuery = Appointment::where('status', 'confirmed');
        if ($startDate) { $projectedQuery->whereDate('appointment_date', '>=', $startDate); }
        if ($endDate) { $projectedQuery->whereDate('appointment_date', '<=', $endDate); }
        $projectedRevenue = $projectedQuery->get()->sum('final_price');

        return view('admin.expenses.index', compact(
            'expenses', 
            'grossRevenue', 
            'totalExpenses', 
            'netProfit', 
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
        ]);

        Expense::create($request->all());

        return back()->with('success', 'Gasto registrado correctamente.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Gasto eliminado.');
    }
}
