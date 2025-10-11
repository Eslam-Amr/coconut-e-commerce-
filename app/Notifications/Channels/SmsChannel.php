<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmsChannel
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
        Log::info('SmsChannel send method called', ['notifiable' => $notifiable, 'notification' => $notification]);
        // dd($notifiable, $notification);
        try {
            if (method_exists($notification, 'toSms')) {
                $smsMessage = $notification->toSms($notifiable);
                Log::info('Sms sent successfully', [
                    // 'phone' => $notifiable->phone,
                    'message' => $smsMessage->message,
                    'otp' => $smsMessage->otp,
                    'user' => $notifiable,
                    'notification' => $notification,
                ]);
            } else {
                Log::error('Notification does not have toSms method');
            }
        } catch (\Exception $e) {
            Log::error('Sms sending failed', [
                'phone' => $notifiable->phone ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

    }
}
