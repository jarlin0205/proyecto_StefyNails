<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Notification;

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
        
        $latestsAppointments = Appointment::with('service')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'pendingCount', 
            'confirmedCount', 
            'completedCount', 
            'cancelledCount', 
            'servicesCount', 
            'notifications', 
            'latestsAppointments'
        ));
    }
}
