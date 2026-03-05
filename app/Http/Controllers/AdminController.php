<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Expense;
use App\Models\Professional;
use App\Models\Sale;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $query = Appointment::query();

        // Si es empleado, filtrar solo sus citas
        if ($user->role === 'employee' && $user->professional) {
            $query->where('professional_id', $user->professional->id);
        }

        $pendingCount = (clone $query)->whereIn('status', ['pending', 'pending_admin', 'pending_client'])->count();
        $confirmedCount = (clone $query)->where('status', 'confirmed')->count();
        $completedCount = (clone $query)->where('status', 'completed')->count();
        $cancelledCount = (clone $query)->where('status', 'cancelled')->count();
        
        // Calcular lo producido (Citas completadas + Ventas POS)
        $completedAppointmentsQuery = (clone $query)->where('status', 'completed')->with('service', 'professional', 'products');
        $completedAppointments = $completedAppointmentsQuery->get();
        $appointmentsProduced = $completedAppointments->sum('grand_total');

        // Sumar ventas POS (Ventas realizadas hoy para el total rápido)
        // Usamos rango explícito de Carbon para evitar problemas de zona horaria en SQL
        $startToday = now()->startOfDay();
        $endToday = now()->endOfDay();
        
        $posSalesToday = Sale::whereBetween('created_at', [$startToday, $endToday])->get();
        $posProduced = $posSalesToday->sum('total');

        // Todas las ventas cargadas con sus items para el modal "Detalle de Producción"
        $allSales = Sale::with('items.product')->latest()->get();

        $totalProduced = $appointmentsProduced + $posProduced;

        $professionals = Professional::all(); // Retrieve all professionals
        $servicesCount = Service::count();
        $allServices = Service::all();
        
        $latestsAppointments = (clone $query)->with('service', 'professional')
            ->whereIn('status', ['pending_admin', 'pending_client', 'confirmed'])
            ->get()
            ->sortBy(fn($appointment) => abs($appointment->appointment_date->timestamp - now()->timestamp))
            ->take(8);

        return view('admin.dashboard', compact(
            'pendingCount', 
            'confirmedCount', 
            'completedCount', 
            'cancelledCount', 
            'servicesCount', 
            'allServices',
            'latestsAppointments',
            'totalProduced',
            'posProduced',
            'completedAppointments',
            'allSales',
            'professionals' 
        ));
    }

    public function toggleTestMode(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para esta acción.');
        }
        $current = session('test_mode', false);
        session(['test_mode' => !$current]);
        
        return back()->with('success', 'Modo Prueba ' . (! $current ? 'Activado' : 'Desactivado'));
    }
}
