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
        
        // No enviar notificación extra si es cancelación desde el bot, 
        // ya que el bot da su propia respuesta simple.
        \App\Helpers\WhatsAppHelper::notifyStatusChange($appointment, $validated['status'] !== 'cancelled');

        $message = "✅ *Cita actualizada con éxito.*";
        
        if ($validated['status'] === 'confirmed') {
            $message = "✅ *Cita Confirmada.*\n\nRecuerda estar 10 minutos antes de tu cita. ¡Te esperamos! ✨";
        } elseif ($validated['status'] === 'cancelled') {
            $message = "✅ *Cita cancelada con éxito.*\n\n🌸 *Hola*, lamentamos que no puedas asistir. Recuerda que puedes volver a agendar tu cita en cualquier momento desde nuestra plataforma:\n\n🔗 " . config('app.url');
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
                'message' => 'No encontramos una cita activa vinculada a este número. 🌸 Te invitamos a agendar una nueva cita en nuestra web: ' . config('app.url')
            ], 404);
        }

        // BLOQUEO: Solo permitir reprogramar si está en 'confirmed' o 'pending_client'
        if (!in_array($appointment->status, ['confirmed', 'pending_client'])) {
             return response()->json([
                'success' => false,
                'message' => 'No puedes reprogramar esta cita en su estado actual.'
            ], 403);
        }

        $appointment->update([
            'appointment_date' => $validated['date'],
            'reschedule_reason' => $validated['reason'] ?? 'Reprogramado vía WhatsApp',
            'status' => 'confirmed' // Al reprogramar desde WhatsApp, vuelve a estar confirmada
        ]);

        \App\Helpers\WhatsAppHelper::notifyReschedule($appointment, 'client');

        return response()->json([
            'success' => true,
            'message' => "📅 *Tu cita ha sido reprogramada para el " . Carbon::parse($validated['date'])->format('d/m/Y h:i A') . "*",
            'appointment' => $appointment
        ]);
    }

    /**
     * Get Link for appointment.
     * Expected JSON: { "phone": "..." }
     */
    public function getLink(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string'
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $last10 = substr($phone, -10);

        $appointment = Appointment::where(function($q) use ($phone, $last10) {
                $q->where('customer_phone', 'LIKE', "%$phone%")
                  ->orWhere('customer_phone', 'LIKE', "%$last10%");
            })
            ->where('appointment_date', '>=', now()->startOfDay())
            ->whereNotIn('status', ['completed', 'cancelled', 'checked_in'])
            ->orderBy('appointment_date', 'asc')
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita activa.'
            ]);
        }

        return response()->json([
            'success' => true,
            'customer_name' => $appointment->customer_name,
            'link' => route('appointments.reschedule.form', $appointment->reschedule_token),
            'appointment' => $appointment
        ]);
    }
}
