<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Helpers\WhatsAppHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:process-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends reminders 20 mins before and auto-cancels no-shows 5 mins before.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        // 1. PROCESS REMINDERS (T - 20 minutes)
        // Range: between 20 and 21 minutes from now, not sent yet.
        $startTimeReminder = $now->copy()->addMinutes(20);
        $endTimeReminder = $now->copy()->addMinutes(21);
        
        $toRemind = Appointment::where('status', 'confirmed')
            ->where('reminder_sent', false)
            ->whereBetween('appointment_date', [$startTimeReminder, $endTimeReminder])
            ->get();
            
        foreach ($toRemind as $appointment) {
            $this->info("Sending reminder to {$appointment->customer_name} (#{$appointment->id})");
            WhatsAppHelper::sendReminder($appointment);
            $appointment->update(['reminder_sent' => true]);
        }

        // 2. PROCESS AUTO-CANCELLATIONS (T - 5 minutes)
        // Range: between 4 and 5 minutes from now, reminder sent, but attendance NOT confirmed.
        $startTimeCancel = $now->copy()->addMinutes(4);
        $endTimeCancel = $now->copy()->addMinutes(5);
        
        $toCancel = Appointment::where('status', 'confirmed')
            ->where('reminder_sent', true)
            ->where('attendance_confirmed', false)
            ->where('appointment_date', '<=', $endTimeCancel)
            ->get();
            
        foreach ($toCancel as $appointment) {
            $this->warn("Auto-cancelling appointment #{$appointment->id} for {$appointment->customer_name} (No-show)");
            
            $appointment->update([
                'status' => 'cancelled',
                'notes' => $appointment->notes . "\n[Cancelada automáticamente por inasistencia faltando 5 min]"
            ]);
            
            // Notify client about cancellation
            $cancelMsg = "🌸 *Hola {$appointment->customer_name}* 🌸\n\nTu cita programada para las *" . $appointment->appointment_date->format('h:i A') . "* ha sido *cancelada automáticamente* debido a que no confirmaste tu asistencia a tiempo. 😔\n\nHemos liberado el espacio para otros clientes. ¡Esperamos verte pronto en otra oportunidad! ✨";
            WhatsAppHelper::sendMessage($appointment->customer_phone, $cancelMsg);
            
            // Notify Admin via Notification model
            \App\Models\Notification::create([
                'appointment_id' => $appointment->id,
                'title' => "Cita Cancelada Automáticamente",
                'message' => "La cita de {$appointment->customer_name} fue cancelada por no confirmar asistencia a tiempo.",
                'type' => 'danger',
                'action_url' => route('admin.appointments.show', $appointment->id)
            ]);
        }
    }
}
