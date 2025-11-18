<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'test:email {recipient}';
    protected $description = 'Send a test email';

    public function handle()
    {
        $recipient = $this->argument('recipient');
        
        try {
            Mail::raw('This is a test email from the Clínica application.', function ($message) use ($recipient) {
                $message->to($recipient)
                        ->subject('Test Email - Clínica');
            });
            
            $this->info("Test email sent successfully to {$recipient}");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
        }
    }
}