<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Appointment;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send reminder emails for appointments scheduled for tomorrow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow();
        $tomorrowStart = $tomorrow->startOfDay();
        $tomorrowEnd = $tomorrow->endOfDay();

        // Get all appointments scheduled for tomorrow
        $appointments = Appointment::whereBetween('scheduled_at', [$tomorrowStart, $tomorrowEnd])
            ->whereIn('status', ['programada', 'confirmada'])
            ->with('patient')
            ->get();

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($appointments as $appointment) {
            if ($this->sendReminderEmail($appointment)) {
                $sentCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->info("Appointment reminders processed:");
        $this->info("- Sent: {$sentCount}");
        $this->info("- Skipped: {$skippedCount}");
        
        Log::info("Appointment reminders sent: {$sentCount}, skipped: {$skippedCount}");

        return 0;
    }

    /**
     * Send reminder email for a specific appointment
     */
    private function sendReminderEmail($appointment)
    {
        try {
            $patient = $appointment->patient;
            if (!$patient || !$patient->email) {
                return false;
            }

            // Check if patient has opted in for email notifications
            $key = 'patient_notifications_' . $patient->id;
            $prefs = Cache::get($key);
            if (!$prefs || !$prefs['enabled'] || !$prefs['email']) {
                return false;
            }

            // Check if we've already sent a reminder for this appointment
            $reminderKey = 'reminder_sent_' . $appointment->id;
            if (Cache::has($reminderKey)) {
                return false; // Already sent
            }

            $scheduledAt = Carbon::parse($appointment->scheduled_at);
            $dateStr = $scheduledAt->format('d/m/Y');
            $timeStr = $scheduledAt->format('H:i');
            
            $subject = 'Recordatorio de Cita - Mañana';
            
            $message = "Estimado/a {$patient->first_name} {$patient->last_name},\n\n";
            $message .= "Le recordamos que tiene una cita programada para mañana {$dateStr} a las {$timeStr}.\n\n";
            
            if ($appointment->reason) {
                $message .= "Motivo: {$appointment->reason}\n\n";
            }
            
            $message .= "Por favor, llegue 15 minutos antes de su cita.\n\n";
            $message .= "Si necesita reprogramar o cancelar su cita, póngase en contacto con nosotros lo antes posible.\n\n";
            $message .= "Saludos,\nClínica";

            // Send the email
            Mail::raw($message, function ($mail) use ($patient, $subject) {
                $mail->to($patient->email)
                     ->subject($subject);
            });

            // Mark as sent (cache for 7 days to avoid duplicate sends)
            Cache::put($reminderKey, true, 60*24*7);

            return true;

        } catch (\Throwable $e) {
            Log::warning('Failed to send appointment reminder: ' . $e->getMessage());
            return false;
        }
    }
}