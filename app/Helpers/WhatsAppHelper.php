<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppHelper
{
    /**
     * Send a message through the WhatsApp bot.
     */
    public static function sendMessage($phone, $message)
    {
        // Limpiar teléfono (solo dígitos y el símbolo +)
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Si el número ya tiene formato internacional (empieza con +), usarlo directamente
        if (str_starts_with($phone, '+')) {
            // Ya está en formato internacional, solo quitar el + para WhatsApp
            $phone = ltrim($phone, '+');
        } else {
            // No tiene formato internacional, aplicar lógica de Colombia
            // Si el número no empieza con 57 (código de Colombia), agregarlo
            if (!str_starts_with($phone, '57')) {
                // Si el número empieza con 0, quitarlo (ej: 0300 -> 300)
                if (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                // Agregar código de país de Colombia
                $phone = '57' . $phone;
            }
        }
        
        // Validar que el número tenga al menos 10 dígitos
        if (strlen($phone) < 10) {
            Log::error("Número de teléfono inválido: {$phone}. Debe tener al menos 10 dígitos.");
            return;
        }
        
        try {
            Http::post('http://localhost:3000/send-message', [
                'phone' => $phone,
                'message' => $message
            ]);
            Log::info("Mensaje de WhatsApp enviado a: {$phone}");
        } catch (\Exception $e) {
            Log::error("Error enviando WhatsApp a {$phone}: " . $e->getMessage());
        }
    }

    public static function notifyNewAppointment($appointment)
    {
        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y h:i A');
        $location = $appointment->location === 'salon' ? 'En el Salón' : 'A Domicilio';
        
        $msg = "✨ *¡Cita Solicitada con Éxito!* ✨\n\n" .
               "Hola {$appointment->customer_name}, hemos recibido tu solicitud:\n\n" .
               "📋 *Servicio:* {$appointment->service->name}\n" .
               "📅 *Fecha:* {$date}\n" .
               "📍 *Lugar:* {$location}\n\n" .
               "🔔 *Por favor espera la confirmación oficial* por parte de Stefy Nails por este mismo medio.\n\n" .
               "Si necesitas cambiar algo, puedes escribir *MENU* en cualquier momento.";
               
        self::sendMessage($appointment->customer_phone, $msg);
    }

    public static function notifyStatusChange($appointment)
    {
        $status = $appointment->status;
        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y h:i A');
        
        $msg = "";
        
        if ($status === 'confirmed') {
            $msg = "✅ *¡Tu cita ha sido CONFIRMADA!* ✅\n\n" .
                   "Te esperamos el día *{$date}* para tu servicio de *{$appointment->service->name}*.\n\n" .
                   "¡Gracias por elegir Stefy Nails! ✨";
        } elseif ($status === 'cancelled') {
            $msg = "🌸 *Hola {$appointment->customer_name}* 🌸\n\n" .
                   "Lamentamos informarte que por el momento *no contamos con espacios disponibles* para tu cita del día *{$date}*.\n\n" .
                   "¡Nos encantaría atenderte! Te invitamos amablemente a solicitar un nuevo horario en nuestra web o escribiendo *MENU*. ✨\n\n" .
                   "¡Gracias por tu comprensión! 💖";
        }

        if ($msg) {
            self::sendMessage($appointment->customer_phone, $msg);
        }
    }

    public static function notifyReschedule($appointment)
    {
        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y h:i A');
        
        $reasonText = "";
        if ($appointment->reschedule_reason) {
            $reasonText = "*Motivo:* {$appointment->reschedule_reason}\n\n";
        }

        $link = route('public.appointments.reschedule', $appointment->reschedule_token);

        $msg = "📅 *Cita Reprogramada* 📅\n\n" .
               $reasonText .
               "Tu cita ha sido actualizada exitosamente.\n\n" .
               "🆕 *Nueva Fecha:* {$date}\n" .
               "📋 *Servicio:* {$appointment->service->name}\n\n" .
               "Si necesitas volver a cambiar el horario, puedes hacerlo aquí:\n🔗 {$link}\n\n" .
               "¡Te esperamos! ✨";
               
        self::sendMessage($appointment->customer_phone, $msg);
    }
}
