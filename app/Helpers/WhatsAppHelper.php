<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppHelper
{
    /**
     * Send a message through the WhatsApp bot.
     */
    public static function sendMessage($phone, $message, $pdfUrl = null, $pdfBase64 = null, $filename = null)
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
                'message' => $message,
                'pdfUrl' => $pdfUrl,
                'pdfBase64' => $pdfBase64,
                'filename' => $filename
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
        $professional = $appointment->professional ? $appointment->professional->name : 'Por asignar';
        $price = number_format($appointment->final_price, 0, ',', '.');
        
        $msg = "✨ *¡Cita Confirmada!* ✨\n\n" .
               "Hola {$appointment->customer_name}, tu cita ha sido reservada con éxito:\n\n" .
               "📋 *Servicio:* {$appointment->service->name}\n" .
               "💰 *Precio:* \${$price}\n" .
               "👩‍🎨 *Profesional:* {$professional}\n" .
               "📅 *Fecha:* {$date}\n" .
               "📍 *Lugar:* {$location}\n\n" .
               "⏰ *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. ✨\n\n" .
               "✅ *Tu espacio ya está asegurado.* ¡Te esperamos! ✨\n\n" .
               "Si necesitas cambiar algo, puedes escribir *MENU* en cualquier momento.";
               
        self::sendMessage($appointment->customer_phone, $msg);
    }

    public static function notifyStatusChange($appointment)
    {
        $status = $appointment->status;
        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y h:i A');
        
        $msg = "";
        
        if ($status === 'confirmed') {
            $professional = $appointment->professional ? $appointment->professional->name : 'Staff';
            $price = number_format($appointment->final_price, 0, ',', '.');
            
            $msg = "✅ *¡Tu cita ha sido CONFIRMADA!* ✅\n\n" .
                   "Te esperamos el día *{$date}*.\n\n" .
                   "📋 *Servicio:* {$appointment->service->name}\n" .
                   "💰 *Precio:* \${$price}\n" .
                   "👩‍🎨 *Profesional:* {$professional}\n\n" .
                   "⏰ *Recordatorio:* Por favor, llega *10 minutos antes* de la hora acordada para asegurar una excelente atención y distribución del tiempo. ✨\n\n" .
                   "Si necesitas realizar algún cambio, puedes escribir *MENU* en cualquier momento.\n\n" .
                   "¡Gracias por elegir Stefy Nails! ✨";
        } elseif ($status === 'cancelled') {
            $msg = "🌸 *Hola {$appointment->customer_name}* 🌸\n\n" .
                   "Lamentamos informarte que tu cita del día *{$date}* ha sido *CANCELADA*.\n\n" .
                   "¡Nos encantaría atenderte en otra ocasión! Te invitamos amablemente a solicitar un nuevo horario en nuestra web:\n\n" .
                   "🔗 " . config('app.url') . "\n\n" .
                   "¡Gracias por tu comprensión! 💖";
        } else {
             // Otros cambios o actualizaciones generales
             $msg = "✨ *¡Tengo una actualización para tu cita!* ✨\n\n" .
                    "Hola {$appointment->customer_name}, he actualizado tu cita para el:\n" .
                    "📅 *Fecha:* {$date}\n\n" .
                    "⏰ *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. ✨\n\n" .
                    "✨ *Tu espacio ya está asegurado.* Si necesitas realizar algún cambio, puedes escribir *MENU* en cualquier momento.";
        }

        if ($msg) {
            self::sendMessage($appointment->customer_phone, $msg);
        }
    }

    public static function notifyReschedule($appointment, $source = 'admin')
    {
        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y h:i A');
        
        $reasonText = "";
        if ($appointment->reschedule_reason) {
            $reasonText = "*Motivo:* {$appointment->reschedule_reason}\n\n";
        }

        if ($source === 'client') {
            $professional = $appointment->professional ? $appointment->professional->name : 'Staff';
            $price = number_format($appointment->final_price, 0, ',', '.');
            
            $msg = "✅ *¡Reprogramación Confirmada!* ✅\n\n" .
                   "Hola {$appointment->customer_name}, hemos actualizado tu cita:\n\n" .
                   "🆕 *Nueva Fecha:* {$date}\n" .
                   "📋 *Servicio:* {$appointment->service->name}\n" .
                   "💰 *Precio:* \${$price}\n" .
                   "👩‍🎨 *Profesional:* {$professional}\n\n" .
                   "⏰ *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. ✨\n\n" .
                   "✨ *Tu nuevo horario ya está asegurado.* ¡Nos vemos pronto!";
        } else {
            // Cuando el admin reprograma, el estado es pending_client
            $professional = $appointment->professional ? $appointment->professional->name : 'Staff';
            $price = number_format($appointment->final_price, 0, ',', '.');
            
            $msg = "📅 *Cita Reprogramada* 📅\n\n" .
                   $reasonText .
                   "He actualizado tu cita para una mejor atención:\n\n" .
                   "🆕 *Nueva Fecha:* {$date}\n" .
                   "📋 *Servicio:* {$appointment->service->name}\n" .
                   "💰 *Precio:* \${$price}\n" .
                   "👩‍🎨 *Profesional:* {$professional}\n\n" .
                   "⏰ *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. ✨\n\n" .
                   "Si necesitas realizar algún cambio, puedes escribir *MENU* en cualquier momento.";
        }
               
        self::sendMessage($appointment->customer_phone, $msg);
    }

    public static function sendReminder($appointment)
    {
        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A');

        $msg = "⏰ *RECORDATORIO DE TU CITA* ⏰\n\n" .
               "Hola {$appointment->customer_name}, tu cita en Stefy Nails empezará en *20 minutos* ({$date}). ✨\n\n" .
               "¿Cómo deseas proceder? RESPONDE con una opción:\n\n" .
               "✅ *ASISTIRE*\n" .
               "1️⃣ *CANCELAR*\n" .
               "2️⃣ *REPROGRAMAR*\n\n" .
               "⏰ *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. ✨\n\n" .
               "⚠️ *Nota:* Si no confirmas tu asistencia con la palabra *ASISTIRE* a tiempo, el sistema liberará automáticamente tu espacio faltando 5 minutos para la cita. ✨";

        self::sendMessage($appointment->customer_phone, $msg);
    }

    public static function sendInvoice($appointment, $url, $pdfBase64 = null)
    {
        $msg = "🧾 *¡Tu Factura de Stefy Nails!* 🧾\n\n" .
               "Hola {$appointment->customer_name}, adjunto encontrarás el comprobante de tu servicio de *{$appointment->service->name}*. ✨\n\n" .
               "¡Esperamos verte pronto de nuevo! 🌸";

        self::sendMessage($appointment->customer_phone, $msg, $url, $pdfBase64, "Factura_StefyNails_{$appointment->id}.pdf");
    }
}
