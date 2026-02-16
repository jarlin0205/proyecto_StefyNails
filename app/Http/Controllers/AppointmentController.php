<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('service')->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->paginate(10)->withQueryString();
        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $services = \App\Models\Service::where('is_active', true)->get();
        return view('appointments.admin_create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_phone_full' => 'nullable|string|max:20',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:now',
            'location' => 'required|in:salon,home',
            'notes' => 'nullable|string'
        ]);
        
        // Usar el número completo internacional si está disponible
        if ($request->filled('customer_phone_full')) {
            $validated['customer_phone'] = $request->input('customer_phone_full');
        }
        unset($validated['customer_phone_full']);

        $appointment = Appointment::create($validated + ['status' => 'confirmed']);

        \App\Helpers\WhatsAppHelper::notifyNewAppointment($appointment);
        \App\Helpers\WhatsAppHelper::notifyStatusChange($appointment); // Confirmada inmediatamente por admin

        return redirect()->route('admin.appointments.index')->with('success', 'Cita agendada correctamente.');
    }

    public function show(Appointment $appointment)
    {
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        if ($appointment->status === 'completed') {
            return redirect()->route('admin.appointments.show', $appointment)
                ->with('error', '❌ No se puede editar una cita que ya ha sido completada.');
        }
        return view('appointments.edit', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'notes' => 'nullable|string',
            'reason_msg' => 'nullable|string'
        ]);

        // RESTRICTION: No rescheduling for completed or cancelled appointments
        if ($appointment->status === 'completed' || $appointment->status === 'cancelled') {
            return back()->with('error', '❌ No se puede reprogramar una cita que ya ha sido ' . ($appointment->status === 'completed' ? 'completada' : 'cancelada') . '.');
        }

        // --- AVAILABILITY CHECK FOR RESCHEDULING ---
        // Only run check if date is provided (it is required by validation above)
        $requestedStart = \Carbon\Carbon::parse($validated['appointment_date']);
        
        // We need the service duration. The appointment model relationship 'service' should be loaded or accessible.
        // Assuming $appointment->service is available.
        $service = $appointment->service; 
        $durationMinutes = $service ? $service->duration_in_minutes : 60; // Default 60 if somehow missing
        $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

        // Check against existing appointments (ONLY confirmed, pending_client or completed AND THIS APPOINTMENT)
        $conflictingAppointment = Appointment::whereIn('status', ['confirmed', 'completed', 'pending_client'])
            ->where('id', '!=', $appointment->id) // CRITICAL: Exclude itself
            ->whereDate('appointment_date', $requestedStart->format('Y-m-d'))
            ->get()
            ->filter(function ($existingApp) use ($requestedStart, $requestedEnd) {
                $existingStart = \Carbon\Carbon::parse($existingApp->appointment_date);
                $existingDuration = $existingApp->service ? $existingApp->service->duration_in_minutes : 60;
                $existingEnd = $existingStart->copy()->addMinutes($existingDuration);

                // Overlap logic
                return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
            })->first();

        if ($conflictingAppointment) {
            return back()->withInput()->withErrors(['appointment_date' => '⚠️ Horario no disponible. Choca con otra cita existente.']);
        }
        // ---------------------------

        // Logic to append reason to notes and save in dedicated field
        if ($request->filled('reason_msg')) {
            $reason = $request->input('reason_msg');
            $date = now()->format('d/m/Y h:i A');
            $newNote = "[Reprogramado el $date: $reason]";
            
            $validated['reschedule_reason'] = $reason;

            if (!empty($validated['notes'])) {
                $validated['notes'] .= "\n" . $newNote;
            } else {
                $validated['notes'] = $newNote;
            }
        }
        
        // Remove reason_msg from validated array if it's there
        unset($validated['reason_msg']);

        $validated['status'] = 'pending_client'; // Admin reschedules via full edit page

        $appointment->update($validated);
        
        \App\Helpers\WhatsAppHelper::notifyReschedule($appointment, 'admin');

        return redirect()->route('admin.appointments.show', $appointment)
            ->with('success', 'Cita reprogramada y notas actualizadas.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending_admin,pending_client,confirmed,completed,cancelled,rescheduled',
            'appointment_date' => 'nullable|date',
            'notification_id' => 'nullable|exists:notifications,id',
            'reason' => 'nullable|string',
            'reason_msg' => 'nullable|string'
        ]);

        // RESTRICTION: Cannot complete a pending appointment
        if ($validated['status'] === 'completed' && ($appointment->status === 'pending_admin' || $appointment->status === 'pending_client')) {
            return back()->with('error', '❌ No se puede marcar como completada una cita que aún está pendiente por confirmar. Confírmala primero.');
        }

        // --- CONFLICT CHECK FOR CONFIRMATIONS ---
        $dateToCheck = $request->filled('appointment_date') ? $validated['appointment_date'] : $appointment->appointment_date;

        if ($validated['status'] === 'confirmed' || $validated['status'] === 'pending_client') {
            $requestedStart = \Carbon\Carbon::parse($dateToCheck);
            $service = $appointment->service;
            $durationMinutes = $service ? $service->duration_in_minutes : 60;
            $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

            // Check against existing confirmed/completed/pending_client (acting as rescheduled) appointments
            $conflict = Appointment::whereIn('status', ['confirmed', 'completed', 'pending_client'])
                ->where('id', '!=', $appointment->id)
                ->whereDate('appointment_date', $requestedStart->format('Y-m-d'))
                ->with('service') // Cargar servicio para el mensaje
                ->get()
                ->filter(function ($existingApp) use ($requestedStart, $requestedEnd) {
                    $existingStart = \Carbon\Carbon::parse($existingApp->appointment_date);
                    $existingDuration = $existingApp->service ? $existingApp->service->duration_in_minutes : 60;
                    $existingEnd = $existingStart->copy()->addMinutes($existingDuration);
                    return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
                })->first();

            if ($conflict) {
                $conflictTime = $conflict->appointment_date->format('h:i A');
                $conflictDate = $conflict->appointment_date->format('d/m/Y');
                $conflictService = $conflict->service ? $conflict->service->name : 'Servicio';
                
                $errMsg = "⚠️ *HORARIO OCUPADO* ⚠️\n\n" .
                          "Este espacio ya está ocupado por:\n" .
                          "👤 *Cliente:* {$conflict->customer_name}\n" .
                          "💅 *Servicio:* {$conflictService}\n" .
                          "📅 *Fecha:* {$conflictDate}\n" .
                          "⏰ *Hora:* {$conflictTime}\n\n" .
                          "Por favor, elige otro horario o cancela la solicitud.";

                return back()->with('error', $errMsg)
                    ->with('conflict_detected', true)
                    ->with('open_appointment_modal_data', [
                        'id' => $appointment->id,
                        'customer_name' => $appointment->customer_name,
                        'customer_phone' => $appointment->customer_phone,
                        'service_id' => $appointment->service_id,
                        'service_name' => $appointment->service->name,
                        'date' => $appointment->appointment_date->format('d/m/Y h:i A'),
                        'date_raw' => $appointment->appointment_date->format('Y-m-d H:i'),
                        'status' => $appointment->status,
                        'price' => $appointment->offered_price ?? ($appointment->service ? $appointment->service->price : 0),
                        'image' => $appointment->reference_image_path,
                        'notes' => $appointment->notes,
                        'edit_url' => route('admin.appointments.edit', $appointment),
                        'status_url' => route('admin.appointments.updateStatus', $appointment),
                        'delete_url' => route('admin.appointments.destroy', $appointment)
                    ])
                    ->withInput();
            }
        }

        $statusToSet = $validated['status'];
        if ($statusToSet === 'rescheduled') $statusToSet = 'pending_client';
        
        // FORCED LOGIC: If date is changed by admin, it must be pending_client 
        // unless they are explicitly completing or cancelling something.
        if ($request->filled('appointment_date') && !in_array($statusToSet, ['completed', 'cancelled'])) {
            $statusToSet = 'pending_client';
        }

        $updateData = ['status' => $statusToSet];
        
        if ($request->filled('appointment_date')) {
            $updateData['appointment_date'] = $validated['appointment_date'];
        }

        $reason = $request->reason ?? $request->reason_msg;
        if ($reason) {
            $updateData['reschedule_reason'] = $reason;
            $date = now()->format('d/m/Y H:i');
            $statusLabel = [
                'confirmed' => 'Confirmada', // Si el admin lo hace, es confirmada
                'cancelled' => 'Rechazada',
                'completed' => 'Completada',
                'pending_admin' => 'Pendiente (Admin)',
                'pending_client' => 'Pendiente (Cliente)'
            ][$validated['status']] ?? 'Actualizada';

            $newNote = "[$statusLabel el $date: $reason]";
            $updateData['notes'] = $appointment->notes ? $appointment->notes . "\n" . $newNote : $newNote;
        }

        $appointment->update($updateData);

        if ($request->filled('notification_id') || $request->has('notification_id')) {
            $notifId = $request->notification_id ?? $request->query('notification_id');
            if ($notifId) {
                \App\Models\Notification::where('id', $notifId)->delete();
            }
        }

        if ($request->filled('appointment_date')) {
            \App\Helpers\WhatsAppHelper::notifyReschedule($appointment, 'admin');
        } else {
            \App\Helpers\WhatsAppHelper::notifyStatusChange($appointment);
        }

        // Status-specific success messages
        $successMessages = [
            'confirmed' => '¡Cita confirmada exitosamente! ✅',
            'cancelled' => 'Cita rechazada. Se envió el mensaje al cliente.',
            'completed' => 'Cita marcada como completada.',
            'pending_admin' => 'Cita puesta en espera de administrador.',
            'pending_client' => 'Propuesta de horario enviada al cliente. ✨'
        ];

        $successMsg = $successMessages[$validated['status']] ?? 'Cita actualizada correctamente.';

        return back()->with('success', $successMsg);
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|exists:services,id',
            'appointment_id' => 'nullable|exists:appointments,id' // Para excluir la cita actual al reprogramar
        ]);

        $date = \Carbon\Carbon::parse($validated['date'])->format('Y-m-d');
        $service = \App\Models\Service::find($validated['service_id']);
        $serviceDuration = $service ? $service->duration_in_minutes : 60;

        // Obtener todas las citas confirmadas o que el cliente debe confirmar para ese día
        $query = Appointment::whereIn('status', ['confirmed', 'completed', 'pending_client'])
            ->whereDate('appointment_date', $date)
            ->with('service');

        // Excluir la cita actual si estamos reprogramando
        if ($request->filled('appointment_id')) {
            $query->where('id', '!=', $validated['appointment_id']);
        }

        $occupiedSlots = $query->get()->map(function ($appointment) {
            $start = \Carbon\Carbon::parse($appointment->appointment_date);
            $duration = $appointment->service ? $appointment->service->duration_in_minutes : 60;
            $end = $start->copy()->addMinutes($duration);

            return [
                'start' => $start->format('Y-m-d H:i'),
                'end' => $end->format('Y-m-d H:i'),
                'customer' => $appointment->customer_name,
                'service' => $appointment->service ? $appointment->service->name : 'Servicio'
            ];
        });

        return response()->json([
            'occupied_slots' => $occupiedSlots,
            'service_duration' => $serviceDuration
        ]);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return back()->with('success', 'Cita eliminada.');
    }
}
