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
        // Limpiar telÃ©fono (solo dÃ­gitos y el sÃ­mbolo +)
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Si el nÃºmero ya tiene formato internacional (empieza con +), usarlo directamente
        if (str_starts_with($phone, '+')) {
            // Ya estÃ¡ en formato internacional, solo quitar el + para WhatsApp
            $phone = ltrim($phone, '+');
        } else {
            // No tiene formato internacional, aplicar lÃ³gica de Colombia
            // Si el nÃºmero no empieza con 57 (cÃ³digo de Colombia), agregarlo
            if (!str_starts_with($phone, '57')) {
                // Si el nÃºmero empieza con 0, quitarlo (ej: 0300 -> 300)
                if (str_starts_with($phone, '0')) {
                    $phone = substr($phone, 1);
                }
                // Agregar cÃ³digo de paÃ­s de Colombia
                $phone = '57' . $phone;
            }
        }
        
        // Validar que el nÃºmero tenga al menos 10 dÃ­gitos
        if (strlen($phone) < 10) {
            Log::error("NÃºmero de telÃ©fono invÃ¡lido: {$phone}. Debe tener al menos 10 dÃ­gitos.");
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
        $location = $appointment->location === 'salon' ? 'En el SalÃ³n' : 'A Domicilio';
        $professional = $appointment->professional ? $appointment->professional->name : 'Por asignar';
        $price = number_format($appointment->final_price, 0, ',', '.');
        
        $msg = "âœ¨ *Â¡Cita Confirmada!* âœ¨\n\n" .
               "Hola {$appointment->customer_name}, tu cita ha sido reservada con Ã©xito:\n\n" .
               "ðŸ“‹ *Servicio:* {$appointment->service->name}\n" .
               "ðŸ’° *Precio:* \${$price}\n" .
               "ðŸ‘©â€ðŸŽ¨ *Profesional:* {$professional}\n" .
               "ðŸ“… *Fecha:* {$date}\n" .
               "ðŸ“ *Lugar:* {$location}\n\n" .
               "â° *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. âœ¨\n\n" .
               "âœ… *Tu espacio ya estÃ¡ asegurado.* Â¡Te esperamos! âœ¨\n\n" .
               "Si necesitas cambiar algo, puedes escribir *MENU* en cualquier momento.";
               
        self::sendMessage($appointment->customer_phone, $msg);

        // Notificar al Profesional
        if ($appointment->professional && $appointment->professional->phone) {
            $profMsg = "ðŸ†• *Â¡Nueva Cita Asignada!* ðŸ†•\n\n" .
                       "Hola {$appointment->professional->name}, se ha agendado una nueva cita:\n\n" .
                       "ðŸ‘¤ *Cliente:* {$appointment->customer_name}\n" .
                       "ðŸ“‹ *Servicio:* {$appointment->service->name}\n" .
                       "ðŸ“… *Fecha:* {$date}\n" .
                       "ðŸ“ *Lugar:* {$location}\n\n" .
                       "Â¡Que tengas un excelente servicio! âœ¨";
            
            self::sendMessage($appointment->professional->phone, $profMsg);
        }
    }

    public static function notifyStatusChange($appointment, $notifyClient = true)
    {
        $status = $appointment->status;
        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y h:i A');
        
        $msg = "";
        
        if ($status === 'confirmed') {
            $professional = $appointment->professional ? $appointment->professional->name : 'Staff';
            $price = number_format($appointment->final_price, 0, ',', '.');
            
            $msg = "âœ… *Â¡Tu cita ha sido CONFIRMADA!* âœ…\n\n" .
                   "Te esperamos el dÃ­a *{$date}*.\n\n" .
                   "ðŸ“‹ *Servicio:* {$appointment->service->name}\n" .
                   "ðŸ’° *Precio:* \${$price}\n" .
                   "ðŸ‘©â€ðŸŽ¨ *Profesional:* {$professional}\n\n" .
                   "â° *Recordatorio:* Por favor, llega *10 minutos antes* de la hora acordada para asegurar una excelente atenciÃ³n y distribuciÃ³n del tiempo. âœ¨\n\n" .
                   "Si necesitas realizar algÃºn cambio, puedes escribir *MENU* en cualquier momento.\n\n" .
                   "Â¡Gracias por elegir Stefy Nails! âœ¨";
        } elseif ($status === 'cancelled') {
            $msg = "ðŸŒ¸ *Hola {$appointment->customer_name}* ðŸŒ¸\n\n" .
                   "Lamentamos informarte que tu cita del dÃ­a *{$date}* ha sido *CANCELADA*.\n\n" .
                   "Â¡Nos encantarÃ­a atenderte en otra ocasiÃ³n! Te invitamos amablemente a solicitar un nuevo horario en nuestra web:\n\n" .
                   "ðŸ”— " . config('app.url') . "\n\n" .
                   "Â¡Gracias por tu comprensiÃ³n! ðŸ’–";
        } elseif ($status === 'completed' || $status === 'checked_in') {
            // No enviar mensaje de actualizaciÃ³n general cuando la cita finaliza o el cliente llega
            return;
        } else {
             // Otros cambios o actualizaciones generales (ej: pending_client)
             $msg = "âœ¨ *Â¡Tengo una actualizaciÃ³n para tu cita!* âœ¨\n\n" .
                    "Hola {$appointment->customer_name}, he actualizado tu cita para el:\n" .
                    "ðŸ“… *Fecha:* {$date}\n\n" .
                    "â° *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. âœ¨\n\n" .
                    "âœ¨ *Tu espacio ya estÃ¡ asegurado.* Si necesitas realizar algÃºn cambio, puedes escribir *MENU* en cualquier momento.";
        }

        if ($msg && $notifyClient) {
            self::sendMessage($appointment->customer_phone, $msg);
        }

        // Notificar al Profesional sobre CancelaciÃ³n
        if ($status === 'cancelled' && $appointment->professional && $appointment->professional->phone) {
            $profMsg = "âŒ *Cita Cancelada* âŒ\n\n" .
                       "Hola {$appointment->professional->name}, te informamos que la cita con *{$appointment->customer_name}* para el dÃ­a *{$date}* ha sido cancelada.";
            
            self::sendMessage($appointment->professional->phone, $profMsg);
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
            
            $msg = "âœ… *Â¡ReprogramaciÃ³n Confirmada!* âœ…\n\n" .
                   "Hola {$appointment->customer_name}, hemos actualizado tu cita:\n\n" .
                   "ðŸ†• *Nueva Fecha:* {$date}\n" .
                   "ðŸ“‹ *Servicio:* {$appointment->service->name}\n" .
                   "ðŸ’° *Precio:* \${$price}\n" .
                   "ðŸ‘©â€ðŸŽ¨ *Profesional:* {$professional}\n\n" .
                   "â° *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. âœ¨\n\n" .
                   "âœ¨ *Tu nuevo horario ya estÃ¡ asegurado.* Â¡Nos vemos pronto!";
        } else {
            // Cuando el admin reprograma, el estado es pending_client
            $professional = $appointment->professional ? $appointment->professional->name : 'Staff';
            $price = number_format($appointment->final_price, 0, ',', '.');
            
            $msg = "ðŸ“… *Cita Reprogramada* ðŸ“…\n\n" .
                   $reasonText .
                   "He actualizado tu cita para una mejor atenciÃ³n:\n\n" .
                   "ðŸ†• *Nueva Fecha:* {$date}\n" .
                   "ðŸ“‹ *Servicio:* {$appointment->service->name}\n" .
                   "ðŸ’° *Precio:* \${$price}\n" .
                   "ðŸ‘©â€ðŸŽ¨ *Profesional:* {$professional}\n\n" .
                   "â° *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. âœ¨\n\n" .
                   "Si necesitas realizar algÃºn cambio, puedes escribir *MENU* en cualquier momento.";
        }
               
        self::sendMessage($appointment->customer_phone, $msg);

        // Notificar al Profesional sobre ReprogramaciÃ³n
        if ($appointment->professional && $appointment->professional->phone) {
            $type = ($source === 'client') ? "El cliente ha reprogramado" : "Se ha reprogramado";
            $profMsg = "ðŸ”„ *Cita Reprogramada* ðŸ”„\n\n" .
                       "Hola {$appointment->professional->name}, {$type} la cita de *{$appointment->customer_name}*:\n\n" .
                       "ðŸ†• *Nueva Fecha:* {$date}\n" .
                       ($appointment->reschedule_reason ? "\nðŸ“ *Motivo:* {$appointment->reschedule_reason}" : "");
            
            self::sendMessage($appointment->professional->phone, $profMsg);
        }
    }

    public static function sendReminder($appointment)
    {
        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A');

        $msg = "â° *RECORDATORIO DE TU CITA* â°\n\n" .
               "Hola {$appointment->customer_name}, tu cita en Stefy Nails empezarÃ¡ en *20 minutos* ({$date}). âœ¨\n\n" .
               "Â¿CÃ³mo deseas proceder? RESPONDE con una opciÃ³n:\n\n" .
               "âœ… *ASISTIRE*\n" .
               "1ï¸âƒ£ *CANCELAR*\n" .
               "2ï¸âƒ£ *REPROGRAMAR*\n\n" .
               "â° *Recordatorio:* Por favor, llega *10 minutos antes* para cumplir con el flujo de horarios. âœ¨\n\n" .
               "âš ï¸ *Nota:* Si no confirmas tu asistencia con la palabra *ASISTIRE* a tiempo, el sistema liberarÃ¡ automÃ¡ticamente tu espacio faltando 5 minutos para la cita. âœ¨";

        self::sendMessage($appointment->customer_phone, $msg);

        // Notificar al Profesional (Recordatorio)
        if ($appointment->professional && $appointment->professional->phone) {
            $profMsg = "â° *PrÃ³xima Cita* â°\n\n" .
                       "Hola {$appointment->professional->name}, tu cita con *{$appointment->customer_name}* inicia en *20 minutos* ({$date}).";
            
            self::sendMessage($appointment->professional->phone, $profMsg);
        }
    }

    public static function sendInvoice($appointment, $url, $pdfBase64 = null)
    {
        $msg = "ðŸ§¾ *Â¡Tu Factura de Stefy Nails!* ðŸ§¾\n\n" .
               "Hola {$appointment->customer_name}, adjunto encontrarÃ¡s el comprobante de tu servicio de *{$appointment->service->name}*. âœ¨\n\n" .
               "Â¡Esperamos verte pronto de nuevo! ðŸŒ¸";

        self::sendMessage($appointment->customer_phone, $msg, $url, $pdfBase64, "Factura_StefyNails_{$appointment->id}.pdf");
    }
}
