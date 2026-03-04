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

        // Mapear status en espaÃ±ol a inglÃ©s si es necesario
        if ($validated['status'] === 'cancelado') {
            $validated['status'] = 'cancelled';
        }

        $appointment = null;

        if (!empty($validated['id'])) {
            $appointment = Appointment::findOrFail($validated['id']);
        } elseif (!empty($validated['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
            $last10 = substr($phone, -10);
            $appointment = Appointment::where(function($q) use ($phone, $last10) {
                    $q->where('customer_phone', 'LIKE', "%$phone%")
                      ->orWhere('customer_phone', 'LIKE', "%$last10%");
                })
                ->where('appointment_date', '>=', now()->startOfDay())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->orderBy('appointment_date', 'asc')
                ->first();
        }

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita activa vinculada a este nÃºmero. ðŸŒ¸ Te invitamos a agendar una nueva cita en nuestra web: ' . config('app.url')
            ], 404);
        }

        // BLOQUEO: Solo permitir confirmar si estÃ¡ en 'pending_client'
        if ($validated['status'] === 'confirmed' && $appointment->status !== 'pending_client') {
            $msg = ($appointment->status === 'pending_admin') 
                ? 'Tu solicitud de cita aÃºn estÃ¡ pendiente de revisiÃ³n por el administrador. ðŸŒ¸ Por favor espera la confirmaciÃ³n oficial antes de realizar cambios.'
                : 'No puedes confirmar esta cita en su estado actual.';
            
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 403);
        }

        $appointment->update(['status' => $validated['status']]);
        
        // No enviar notificaciÃ³n extra si es cancelaciÃ³n desde el bot, 
        // ya que el bot da su propia respuesta simple.
        \App\Helpers\WhatsAppHelper::notifyStatusChange($appointment, $validated['status'] !== 'cancelled');

        $message = "âœ… *Cita actualizada con Ã©xito.*";
        
        if ($validated['status'] === 'confirmed') {
            $message = "âœ… *Cita Confirmada.*\n\nRecuerda estar 10 minutos antes de tu cita. Â¡Te esperamos! âœ¨";
        } elseif ($validated['status'] === 'cancelled') {
            $message = "âœ… *Cita cancelada con Ã©xito.*\n\nðŸŒ¸ *Hola*, lamentamos que no puedas asistir. Recuerda que puedes volver a agendar tu cita en cualquier momento desde nuestra plataforma:\n\nðŸ”— " . config('app.url');
        }

        return response()->json([
            'success' => true,
            'message' => $message,
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
            $last10 = substr($phone, -10);
            $appointment = Appointment::where(function($q) use ($phone, $last10) {
                    $q->where('customer_phone', 'LIKE', "%$phone%")
                      ->orWhere('customer_phone', 'LIKE', "%$last10%");
                })
                ->where('appointment_date', '>=', now()->startOfDay())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->orderBy('appointment_date', 'asc')
                ->first();
        }

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita activa para reprogramar. ðŸŒ¸ Te invitamos a agendar una nueva cita en nuestra web: ' . config('app.url')
            ], 404);
        }

        $requestedStart = Carbon::parse($validated['date']);
        
        $service = $appointment->service;
        $durationMinutes = $service ? $service->duration_in_minutes : 60;
        $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

        // Conflict check (excluding self) - filtered by professional
        $conflicting = Appointment::whereIn('status', ['confirmed', 'completed', 'pending_client'])
            ->where('id', '!=', $appointment->id)
            ->where('professional_id', $appointment->professional_id)
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
            'status' => 'confirmed', // Confirmado automÃ¡ticamente si hay disponibilidad
            'reschedule_reason' => $validated['reason'] ?? null,
            'notes' => $appointment->notes . "\n[Reprogramado vÃ­a WhatsApp el " . now()->format('d/m/Y H:i') . " de original $oldDate]"
        ]);

        \App\Helpers\WhatsAppHelper::notifyReschedule($appointment, 'client');
        
        // Crear notificaciÃ³n para el administrador
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
            $professionalId = $request->query('professional_id');
            
            if (!$date) return response()->json([]);

            // Si no hay profesional, y hay profesionales activos, podemos tomar el primero por defecto
            // o devolver vacio si es requerido. Para la web de admin/booking mejor requerirlo.
            if (!$professionalId) {
                $firstProf = \App\Models\Professional::where('is_active', true)->first();
                $professionalId = $firstProf ? $firstProf->id : null;
            }

            if (!$professionalId) return response()->json(['busy' => [], 'working_hours' => null]);

            // 1. Get standard busy slots (appointments) for THIS professional
            $appointments = Appointment::whereDate('appointment_date', $date)
                ->where('professional_id', $professionalId)
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

            // 2. Get Custom Availability Override for THIS professional
            $availability = \App\Models\Availability::whereDate('date', $date)
                ->where('professional_id', $professionalId)
                ->first();
            
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
     * Confirm attendance (Check-in).
     * Expected JSON: { "phone": "..." }
     */
    public function checkin(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $last10 = substr($phone, -10);

        \Log::info("Bot Checkin Attempt: phone=$phone, last10=$last10, now=" . now()->toDateTimeString());

        // Find the next upcoming confirmed appointment for this phone within a strict window
        $appointment = Appointment::where(function($q) use ($phone, $last10) {
                $q->where('customer_phone', 'LIKE', "%$phone%")
                  ->orWhere('customer_phone', 'LIKE', "%$last10%");
            })
            ->where('status', 'confirmed')
            ->where('appointment_date', '>=', now()) // Must not be in the past
            ->where('appointment_date', '<=', now()->addMinutes(30)) // Max 30 mins in the future
            ->orderBy('appointment_date', 'asc')
            ->first();

        if (!$appointment) {
            \Log::warning("Bot Checkin Failed: No valid confirmed appointment found for $phone in strict window (now to +30m).");
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita confirmada prÃ³xima a iniciar para este nÃºmero. Recuerda que solo puedes confirmar asistencia hasta 5 minutos antes de tu cita. ðŸŒ¸'
            ], 404);
        }

        $appointment->update(['attendance_confirmed' => true]);

        return response()->json([
            'success' => true,
            'message' => "ðŸŒ¸ *Â¡Excelente, {$appointment->customer_name}!* Tu asistencia ha sido confirmada.\n\nâ° *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. Te esperamos en unos minutos. âœ¨",
            'appointment' => $appointment
        ]);
    }

    /**
     * Get the public reschedule link for a customer's latest appointment.
     */
    public function getRescheduleLink(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->query('phone'));
        
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'TelÃ©fono requerido.'], 400);
        }

        $last10 = substr($phone, -10);
        $appointment = Appointment::where(function($q) use ($phone, $last10) {
                $q->where('customer_phone', 'LIKE', "%$phone%")
                  ->orWhere('customer_phone', 'LIKE', "%$last10%");
            })
            ->where('appointment_date', '>=', now()->startOfDay())
            ->whereIn('status', ['pending_admin', 'pending_client', 'confirmed'])
            ->orderBy('appointment_date', 'asc')
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita activa para reprogramar. ðŸŒ¸ Te invitamos a agendar una nueva cita en nuestra web: ' . config('app.url')
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
