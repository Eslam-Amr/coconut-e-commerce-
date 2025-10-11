<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\OtpNotification;
use App\Services\Utilities\OtpService;
use Illuminate\Support\Facades\Notification;

class UserObserver
{
    public function __construct(
        private OtpService $otpService
    ) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // dd($user->phone);
        // Only send OTP if user has a phone number
        if ($user->phone) {
            $otp = $this->otpService->generate($user->phone);
            // dd($otp);
            if ($otp) {
                Notification::route('sms', $user)
                    ->notify(new OtpNotification($otp, $user, $user->phone, ['sms']));
            }
        }
    }
}
