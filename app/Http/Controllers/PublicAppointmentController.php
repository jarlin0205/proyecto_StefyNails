<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Notification;

class PublicAppointmentController extends Controller
{
    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request)
    {
        $selectedServiceId = $request->query('service_id');
        // Fetch services with their categories, ordered by category name then service name
        $services = Service::with(['category', 'images'])
            ->where('is_active', true)
            ->get()
            ->sortBy(['category.name', 'name']);
        $professionals = \App\Models\Professional::where('is_active', true)->get();
            
        return view('appointments.create', compact('services', 'selectedServiceId', 'professionals'));
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_phone_full' => 'nullable|string|max:20',
            'service_id' => 'required|exists:services,id',
            'professional_id' => 'required|exists:professionals,id',
            'appointment_date' => 'required|date|after_or_equal:now',
            'notes' => 'nullable|string',
            'reference_image_path' => 'nullable|string',
            'location' => 'nullable|string',
            'offered_price' => 'nullable|numeric'
        ]);
        
        // Usar el número completo internacional si está disponible
        if ($request->filled('customer_phone_full')) {
            $validated['customer_phone'] = $request->input('customer_phone_full');
        }
        unset($validated['customer_phone_full']);

        // Check and Logic for Offered Price
        $service = Service::find($validated['service_id']);
        $finalOfferedPrice = null;
        
        // Only allow offers if service price is >= 40000
        if ($request->filled('offered_price') && $service->price >= 40000) {
            $offer = $request->input('offered_price');
            $minAllowed = $service->price - 5000;
            
            // If offer is too low, clamp it to minAllowed
            if ($offer < $minAllowed) {
                $finalOfferedPrice = $minAllowed;
            } else {
                $finalOfferedPrice = $offer;
            }
        }

        // --- AVAILABILITY CHECK ---
        $requestedStart = \Carbon\Carbon::parse($validated['appointment_date']);
        $durationMinutes = $service->duration_in_minutes;
        $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

        // Check against existing appointments (ONLY confirmed, completed or pending_client) for THIS professional
        $conflictingAppointment = Appointment::whereIn('status', ['confirmed', 'completed', 'pending_client'])
            ->where('professional_id', $validated['professional_id'])
            ->whereDate('appointment_date', $requestedStart->format('Y-m-d')) // Optimización basic
            ->get()
            ->filter(function ($existingApp) use ($requestedStart, $requestedEnd) {
                $existingStart = \Carbon\Carbon::parse($existingApp->appointment_date);
                // Calculate existing end time based on ITS service duration
                // We need to fetch service duration for existing appointment too
                // Assuming relationship exists and service is loaded or we load it. 
                // Appointment belongsTo Service.
                $existingDuration = $existingApp->service ? $existingApp->service->duration_in_minutes : 60;
                $existingEnd = $existingStart->copy()->addMinutes($existingDuration);

                // Overlap logic:
                // (StartA <= EndB) and (EndA >= StartB) is actually overlap if strictly less/greater usually
                // Standard overlap: (StartA < EndB) && (EndA > StartB)
                return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
            })->first();

        if ($conflictingAppointment) {
            return back()->with('error', '⚠️ Lo sentimos, ese horario no está disponible. Ya existe una cita agendada que choca con la duración de tu servicio. Por favor intenta otra hora.')->withInput();
        }
        // ---------------------------

        $appointment = Appointment::create([
            'service_id' => $validated['service_id'],
            'professional_id' => $validated['professional_id'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'location' => $validated['location'] ?? 'salon',
            'offered_price' => $finalOfferedPrice,
            'appointment_date' => $validated['appointment_date'],
            'notes' => $validated['notes'],
            'status' => 'pending_admin',
            'reference_image_path' => $validated['reference_image_path'],
        ]);

        // Create notification for admin
        // Create notification for admin
        Notification::create([
            'appointment_id' => $appointment->id,
            'title' => "{$appointment->customer_name} ha agendado una nueva cita",
            'message' => "Servicio: {$appointment->service->name} - Fecha: {$appointment->appointment_date->format('d/m/Y H:i')}",
            'type' => 'info',
            'action_url' => route('admin.appointments.show', $appointment->id)
        ]);

        \App\Helpers\WhatsAppHelper::notifyNewAppointment($appointment);

        return redirect()->route('home')->with('success', '¡Cita solicitada con éxito! Te contactaremos pronto o recibirás un mensaje de nuestro bot.');
    }

    /**
     * Show the reschedule form for the client.
     */
    public function reschedule($token)
    {
        $appointment = Appointment::where('reschedule_token', $token)->firstOrFail();

        if ($appointment->status === 'completed' || $appointment->status === 'cancelled') {
            return redirect()->route('home')->with('error', '❌ Esta cita ya no puede ser reprogramada.');
        }

        return view('appointments.public_reschedule', compact('appointment'));
    }

    /**
     * Update the appointment date from the public form.
     */
    public function updateReschedule(Request $request, $token)
    {
        $appointment = Appointment::where('reschedule_token', $token)->firstOrFail();

        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:now',
            'reschedule_reason' => 'nullable|string|max:255',
        ]);

        // Availability check logic (similar to store/update)
        $requestedStart = \Carbon\Carbon::parse($validated['appointment_date']);
        $service = $appointment->service;
        $durationMinutes = $service ? $service->duration_in_minutes : 60;
        $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

        $conflict = Appointment::whereIn('status', ['confirmed', 'completed', 'pending_client'])
            ->where('id', '!=', $appointment->id)
            ->where('professional_id', $appointment->professional_id)
            ->whereDate('appointment_date', $requestedStart->format('Y-m-d'))
            ->get()
            ->filter(function ($existingApp) use ($requestedStart, $requestedEnd) {
                $existingStart = \Carbon\Carbon::parse($existingApp->appointment_date);
                $existingDuration = $existingApp->service ? $existingApp->service->duration_in_minutes : 60;
                $existingEnd = $existingStart->copy()->addMinutes($existingDuration);
                return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
            })->first();

        if ($conflict) {
            return back()->with('error', '⚠️ Horario no disponible. Por favor elige otra hora.')->withInput();
        }

        $oldDate = $appointment->appointment_date->format('d/m/Y h:i A');
        $newDate = $requestedStart->format('d/m/Y h:i A');
        $now = now()->format('d/m/Y h:i A');

        $reason = $validated['reschedule_reason'] ?? 'Reprogramado por el cliente';
        $newNote = "[Cliente reprogramó el $now: de $oldDate a $newDate. Motivo: $reason]";

        $appointment->update([
            'appointment_date' => $requestedStart,
            'reschedule_reason' => $reason,
            'notes' => $appointment->notes ? $appointment->notes . "\n" . $newNote : $newNote,
            'status' => 'pending_admin', 
            'reschedule_token' => \Illuminate\Support\Str::random(32), // Invalida el link actual
        ]);

        // Create notification for admin
        Notification::create([
            'appointment_id' => $appointment->id,
            'title' => "{$appointment->customer_name} ha reprogramado su cita",
            'message' => "Nueva Fecha: $newDate (Anterior: $oldDate)",
            'type' => 'warning',
            'action_url' => route('admin.appointments.show', $appointment->id)
        ]);

        \App\Helpers\WhatsAppHelper::notifyReschedule($appointment, 'client');

        return redirect()->route('home')->with('success', '¡Tu cita ha sido reprogramada con éxito! ✨');
    }
}

