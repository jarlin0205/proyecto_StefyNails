<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Availability;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WhatsAppBotController extends Controller
{
    public function __construct()
    {
        // Forzar que los enlaces generados usen la APP_URL del .env
        \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
    }

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
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(customer_phone, '+', ''), ' ', ''), '-', '') LIKE ?", ["%$last10"]);
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
        
        // Create notification for admin on cancellation
        if ($validated['status'] === 'cancelled') {
            Notification::create([
                'appointment_id' => $appointment->id,
                'title' => "{$appointment->customer_name} ha cancelado su cita (Bot)",
                'message' => "Servicio: {$appointment->service->name} - Fecha: {$appointment->appointment_date->format('d/m/Y H:i')}",
                'type' => 'danger',
                'action_url' => route('admin.appointments.show', $appointment->id)
            ]);
        }

        // No enviar notificación extra si es cancelación desde el bot, 
        // ya que el bot da su propia respuesta simple.
        \App\Helpers\WhatsAppHelper::notifyStatusChange($appointment, $validated['status'] !== 'cancelled');

        $message = "✅ *Cita actualizada con éxito.*";
        
        if ($validated['status'] === 'confirmed') {
            $message = "✅ *Cita Confirmada.*\n\nRecuerda estar 10 minutos antes de tu cita. ¡Te esperamos! ✨";
        } elseif ($validated['status'] === 'cancelled') {
            $message = "✅ *Cita cancelada con éxito.*\n\n🌸 *Hola*, lamentamos que no puedas asistir. Recuerda que puedes volver a agendar tu cita en *cualquier momento* y desde *cualquier lugar* en nuestra plataforma:\n\n🔗 " . config('app.url') . "\n\n¡Esperamos verte pronto! ✨";
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
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(customer_phone, '+', ''), ' ', ''), '-', '') LIKE ?", ["%$last10"]);
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

        // Create notification for admin on reschedule
        Notification::create([
            'appointment_id' => $appointment->id,
            'title' => "{$appointment->customer_name} ha reprogramado su cita (Bot)",
            'message' => "Nueva Fecha: " . Carbon::parse($validated['date'])->format('d/m/Y h:i A'),
            'type' => 'warning',
            'action_url' => route('admin.appointments.show', $appointment->id)
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
    public function getRescheduleLink(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string'
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $last10 = substr($phone, -10);

        $appointment = Appointment::where(function($q) use ($last10) {
                $q->whereRaw("REPLACE(REPLACE(REPLACE(customer_phone, '+', ''), ' ', ''), '-', '') LIKE ?", ["%$last10"]);
            })
            ->where('appointment_date', '>=', now()->startOfDay())
            ->whereNotIn('status', ['completed', 'cancelled'])
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
            'link' => route('public.appointments.reschedule', $appointment->reschedule_token),
            'appointment' => $appointment
        ]);
    }

    /**
     * Get busy slots and custom availability for a date.
     * Expected Query: ?date=2026-03-04&professional_id=1
     */
    public function getBusySlots(Request $request)
    {
        $date = $request->query('date');
        $professionalId = $request->query('professional_id');

        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        $query = Appointment::with('service')
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'completed']);

        if ($professionalId) {
            $query->where('professional_id', $professionalId);
        }

        $appointments = $query->get();

        $busy = $appointments->map(function ($app) {
            $start = $app->appointment_date->format('H:i');
            // Use actual service duration, fallback to 60 min if no service linked
            $durationMins = ($app->service && $app->service->duration_in_minutes > 0)
                ? $app->service->duration_in_minutes
                : 60;
            $end = $app->appointment_date->copy()->addMinutes($durationMins)->format('H:i');
            return [
                'start' => $start,
                'end'   => $end,
                'title' => 'Ocupado',
                'duration_minutes' => $durationMins,
            ];
        });

        // Buscar disponibilidad personalizada
        $avail = Availability::where('date', $date);
        if ($professionalId) {
            $avail->where('professional_id', $professionalId);
        }
        $avail = $avail->first();

        return response()->json([
            'busy' => $busy,
            'working_hours' => $avail ? $avail->active_slots : null,
            'message' => $avail ? $avail->message : null
        ]);
    }

    /**
     * Confirm attendance via bot.
     * Updates attendance_confirmed=true to prevent auto-cancellation.
     */
    public function checkin(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string'
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $last10 = substr($phone, -10);

        $appointment = Appointment::where(function($q) use ($last10) {
                $q->whereRaw("REPLACE(REPLACE(REPLACE(customer_phone, '+', ''), ' ', ''), '-', '') LIKE ?", ["%$last10"]);
            })
            ->whereDate('appointment_date', Carbon::today())
            ->where('status', 'confirmed') // Solo citas aún confirmadas
            ->where('attendance_confirmed', false)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'No encontramos una cita para hoy pendiente de confirmar.'
            ], 404);
        }

        $appointment->update(['attendance_confirmed' => true]);

        return response()->json([
            'success' => true,
            'message' => "✅ *¡Asistencia Confirmada!* Hemos registrado tu respuesta. Te esperamos pronto en tu cita. 🌸",
            'appointment' => $appointment
        ]);
    }

    /**
     * Report emergency from bot.
     */
    public function reportEmergency(Request $request)
    {
        $validated = $request->validate([
            'error' => 'required|string',
            'consecutive_errors' => 'required|integer',
            'timestamp' => 'required|string'
        ]);

        $msg = "🚨 EMERGENCIA BOT WHATSAPP: {$validated['consecutive_errors']} errores consecutivos detectados.\n";
        $msg .= "Fecha: {$validated['timestamp']}\n";
        $msg .= "Último Error: {$validated['error']}";

        Log::critical($msg);

        // Intentar enviar email si hay destinatario configurado
        $adminEmail = config('services.bot.alert_email'); 
        if ($adminEmail) {
            try {
                Mail::raw($msg, function ($m) use ($adminEmail) {
                    $m->to($adminEmail)->subject('⚠️ ALERTA CRÍTICA: Bot de WhatsApp');
                });
            } catch (\Exception $e) {
                Log::error("No se pudo enviar email de emergencia: " . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get dynamic config for the bot.
     */
    public function getBotConfig()
    {
        return response()->json([
            'success' => true,
            'admin_phone' => config('services.bot.admin_phone'),
        ]);
    }
}
