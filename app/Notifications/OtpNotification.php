<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;
 
    public $channels;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $otp,
        public ?User $user = null,
        public ?string $phone = null,
        $channels = ['email','sms']
    ) {        
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $recipientEmail = $this->user ? $this->user->email : $notifiable->routes['email'];
        $recipientName = $this->user ? $this->user->name : 'User';

        Mail::send('emails.otp', [
            'otp' => $this->otp->otp_code,
            'name' => $recipientName,
        ], function($message) use ($recipientEmail) {
            $message->to($recipientEmail)
                   ->subject('Your OTP Code');
        });
    }
    public function toSms(object $notifiable)
    {
        Log::info('toSms method called', ['notifiable' => $notifiable]);
        // dd($notifiable);
        // Create a simple SMS message object
        $smsMessage = new \stdClass();
        $smsMessage->message = 'Your OTP Code is ' . $this->otp->otp_code;
        $smsMessage->otp = $this->otp->otp_code;
        $smsMessage->phone = $this->phone ?? $notifiable->phone ?? 'unknown';
        
        Log::info('Sms sent successfully', [
            'message' => $smsMessage->message,
            'otp' => $smsMessage->otp,
            'phone' => $smsMessage->phone,
            'user' => $notifiable,
            'notification' => $this,
        ]);
        
        return $smsMessage;
        
    }



    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'otp' => $this->otp,
            'name' => $this->user ? $this->user->name : 'User',
            'expires_in' => '10 minutes',
        ];
    }
}
