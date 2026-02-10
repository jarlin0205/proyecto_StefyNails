<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Appointment;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::latest()->paginate(20);

        // Financial Indicators (same as AdminController)
        $grossRevenue = Appointment::where('status', 'completed')->get()->sum('final_price');
        $totalExpenses = Expense::sum('amount');
        $netProfit = $grossRevenue - $totalExpenses;
        $projectedRevenue = Appointment::where('status', 'confirmed')->get()->sum('final_price');

        return view('admin.expenses.index', compact(
            'expenses', 
            'grossRevenue', 
            'totalExpenses', 
            'netProfit', 
            'projectedRevenue'
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
