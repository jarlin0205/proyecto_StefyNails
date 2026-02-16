<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WhatsAppBotController extends Controller
{
    /**
     * Update appointment status.
     * Expected JSON: { "id": 1, "status": "confirmed", "phone": "..." }
     */
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:appointments,id',
            'phone' => 'nullable|string',
            'status' => 'required|in:confirmed,cancelled,completed,pending_admin,pending_client,cancelado'
        ]);

        // Mapear status en español a inglés si es necesario
        if ($validated['status'] === 'cancelado') {
            $validated['status'] = 'cancelled';
        }

        $appointment = null;

        if (!empty($validated['id'])) {
            $appointment = Appointment::findOrFail($validated['id']);
        } elseif (!empty($validated['phone'])) {
            // Normalizar teléfono (remover prefijos si es necesario, aquí asumimos coincidencia o LIKE)
            $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
            // El bot envía el número internacional. Buscamos citas recientes con ese número.
            $appointment = Appointment::where(function($q) use ($phone) {
                    $q->where('customer_phone', 'LIKE', "%$phone%")
                      ->orWhere('customer_phone', 'LIKE', '%' . substr($phone, -10) . '%');
                })
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->latest()
                ->first();
        }

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita activa vinculada a este número. 🌸 Te invitamos a agendar una nueva cita en nuestra web: ' . config('app.url')
            ], 404);
        }

        // BLOQUEO: Solo permitir confirmar si está en 'pending_client'
        if ($validated['status'] === 'confirmed' && $appointment->status !== 'pending_client') {
            $msg = ($appointment->status === 'pending_admin') 
                ? 'Tu solicitud de cita aún está pendiente de revisión por el administrador. 🌸 Por favor espera la confirmación oficial antes de realizar cambios.'
                : 'No puedes confirmar esta cita en su estado actual.';
            
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 403);
        }

        $appointment->update(['status' => $validated['status']]);
        \App\Helpers\WhatsAppHelper::notifyStatusChange($appointment);

        return response()->json([
            'success' => true,
            'message' => "Estado de la cita #{$appointment->id} actualizado a {$validated['status']}",
            'appointment' => $appointment
        ]);
    }

    /**
     * Reschedule appointment.
     * Expected JSON: { "id": 1, "date": "2026-02-01 14:00", "phone": "..." }
     */
    public function reschedule(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:appointments,id',
            'phone' => 'nullable|string',
            'date' => 'required|date|after_or_equal:now',
            'reason' => 'nullable|string'
        ]);

        $appointment = null;

        if (!empty($validated['id'])) {
            $appointment = Appointment::findOrFail($validated['id']);
        } elseif (!empty($validated['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
            $appointment = Appointment::where(function($q) use ($phone) {
                    $q->where('customer_phone', 'LIKE', "%$phone%")
                      ->orWhere('customer_phone', 'LIKE', '%' . substr($phone, -10) . '%');
                })
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->latest()
                ->first();
        }

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita activa para reprogramar. 🌸 Te invitamos a agendar una nueva cita en nuestra web: ' . config('app.url')
            ], 404);
        }

        $requestedStart = Carbon::parse($validated['date']);
        
        $service = $appointment->service;
        $durationMinutes = $service ? $service->duration_in_minutes : 60;
        $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

        // Conflict check (excluding self) - confirmed, completed, and pending_client (acting as scheduled)
        $conflicting = Appointment::whereIn('status', ['confirmed', 'completed', 'pending_client'])
            ->where('id', '!=', $appointment->id)
            ->whereDate('appointment_date', $requestedStart->format('Y-m-d'))
            ->get()
            ->filter(function ($existingApp) use ($requestedStart, $requestedEnd) {
                $existingStart = Carbon::parse($existingApp->appointment_date);
                $existingDuration = $existingApp->service ? $existingApp->service->duration_in_minutes : 60;
                $existingEnd = $existingStart->copy()->addMinutes($existingDuration);
                return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
            })->first();

        if ($conflicting) {
            return response()->json([
                'success' => false,
                'message' => 'Horario no disponible. Choca con otra cita.'
            ], 422);
        }

        $oldDate = $appointment->appointment_date->format('d/m/Y H:i');
        $appointment->update([
            'appointment_date' => $requestedStart,
            'status' => 'pending_admin', // Regresa a pendiente para aprobación del administrador
            'reschedule_reason' => $validated['reason'] ?? null,
            'notes' => $appointment->notes . "\n[Reprogramado vía WhatsApp el " . now()->format('d/m/Y H:i') . " de original $oldDate]"
        ]);

        \App\Helpers\WhatsAppHelper::notifyReschedule($appointment, 'client');
        
        // Crear notificación para el administrador
        Notification::create([
            'appointment_id' => $appointment->id,
            'title' => "{$appointment->customer_name} ha reprogramado su cita (WhatsApp)",
            'message' => "Nueva Fecha: " . $requestedStart->format('d/m/Y h:i A') . " (Anterior: $oldDate)",
            'type' => 'warning',
            'action_url' => route('admin.appointments.show', $appointment->id)
        ]);

        return response()->json([
            'success' => true,
            'message' => "Cita #{$appointment->id} reprogramada para " . $requestedStart->format('d/m/Y h:i A'),
            'appointment' => $appointment
        ]);
    }

    /**
     * Get busy slots for a specific date.
     */
    public function getBusySlots(Request $request)
    {
        try {
            $date = $request->query('date');
            if (!$date) return response()->json([]);

            // 1. Get standard busy slots (appointments)
            $appointments = Appointment::whereDate('appointment_date', $date)
                ->whereIn('status', ['confirmed', 'completed', 'pending_client'])
                ->with('service')
                ->get();

            $busy = $appointments->map(function ($app) {
                $start = Carbon::parse($app->appointment_date);
                $duration = $app->service ? $app->service->duration_in_minutes : 60;
                return [
                    'start' => $start->format('H:i'),
                    'end' => $start->copy()->addMinutes($duration)->format('H:i')
                ];
            });

            // 2. Get Custom Availability Override
            $availability = \App\Models\Availability::whereDate('date', $date)->first();
            
            return response()->json([
                'busy' => $busy,
                'working_hours' => $availability ? $availability->active_slots : null,
                'message' => $availability ? $availability->message : null
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching busy slots: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Get the public reschedule link for a customer's latest appointment.
     */
    public function getRescheduleLink(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->query('phone'));
        
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Teléfono requerido.'], 400);
        }

        $appointment = Appointment::where(function($q) use ($phone) {
                $q->where('customer_phone', 'LIKE', "%$phone%")
                  ->orWhere('customer_phone', 'LIKE', '%' . substr($phone, -10) . '%');
            })
            ->whereIn('status', ['pending_admin', 'pending_client', 'confirmed'])
            ->latest()
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita activa para reprogramar. 🌸 Te invitamos a agendar una nueva cita en nuestra web: ' . config('app.url')
            ], 404);
        }

        $link = config('app.url') . '/reprogramar/' . $appointment->reschedule_token;

        return response()->json([
            'success' => true,
            'link' => $link,
            'appointment_id' => $appointment->id,
            'customer_name' => $appointment->customer_name
        ]);
    }
}
