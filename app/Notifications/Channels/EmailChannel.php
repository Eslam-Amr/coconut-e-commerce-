<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification): void
    {
        try {
            if (method_exists($notification, 'toMail')) {
                $mailMessage = $notification->toMail($notifiable);
            } else {
                Log::error('Notification does not have toMail method');
            }
        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'email' => $notifiable->email ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

    }
}
