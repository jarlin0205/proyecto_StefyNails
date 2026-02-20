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
        
        // Calcular lo producido (solo citas completadas)
        $totalProduced = (clone $query)->where('status', 'completed')
            ->get()
            ->sum(function($appointment) {
                return $appointment->offered_price ?? ($appointment->service ? $appointment->service->price : 0);
            });

        $servicesCount = Service::count();
        $notifications = Notification::where('is_read', false)->get();
        
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
            'notifications', 
            'latestsAppointments',
            'totalProduced'
        ));
    }
}
