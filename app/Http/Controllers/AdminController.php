<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Expense;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pendingCount = Appointment::where('status', 'pending')->count();
        $confirmedCount = Appointment::where('status', 'confirmed')->count();
        $completedCount = Appointment::where('status', 'completed')->count();
        $cancelledCount = Appointment::where('status', 'cancelled')->count();
        
        $servicesCount = Service::count();
        $notifications = Notification::where('is_read', false)->get();
        
        $latestsAppointments = Appointment::with('service')
            ->whereIn('status', ['pending_admin', 'pending_client', 'confirmed'])
            ->get()
            ->sortBy(fn($appointment) => abs($appointment->appointment_date->timestamp - now()->timestamp))
            ->take(8);

        // Financial Indicators
        $grossRevenue = Appointment::where('status', 'completed')->get()->sum('final_price');
        $totalExpenses = Expense::sum('amount');
        $netProfit = $grossRevenue - $totalExpenses;
        $projectedRevenue = Appointment::where('status', 'confirmed')->get()->sum('final_price');

        // Monthly Stats (Current Month)
        $monthlyRevenue = Appointment::where('status', 'completed')
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->get()
            ->sum('final_price');

        return view('admin.dashboard', compact(
            'pendingCount', 
            'confirmedCount', 
            'completedCount', 
            'cancelledCount', 
            'servicesCount', 
            'notifications', 
            'latestsAppointments',
            'grossRevenue',
            'totalExpenses',
            'netProfit',
            'projectedRevenue',
            'monthlyRevenue'
        ));
    }
}
