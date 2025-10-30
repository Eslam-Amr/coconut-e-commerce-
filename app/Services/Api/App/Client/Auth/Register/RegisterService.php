<?php

namespace App\Services\Api\App\Client\Auth\Register;

use App\Models\User;
use App\Notifications\OtpNotification;
use App\Services\Utilities\OtpService;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class RegisterService
{
    use ApiResponseTrait;
    public function __construct(public OtpService $otpService) {}
    public function register(array $data): JsonResponse
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);
            return $this->successResponse($user, __('messages.success'));
        });
    }
    
    public function resendOtp($data)
    {
        $this->otpService->deleteOldOtps($data['phone']);
        $otp = $this->otpService->generate($data['phone']);
        Notification::route('sms', $data['phone'])
            ->notify(new OtpNotification($otp, null, $data['phone'], ['sms']));

        return $this->successResponse($otp, __('messages.otp_sent_to_sms'));
    }
    public function verifyOtp($data)
    {
        $otp = $this->otpService->verify($data['phone'], $data['otp']);
        if ($otp['success'])
            return $this->successResponse($otp, __('messages.auth.otp.verified'));
        return $this->errorResponse($otp, __('messages.auth.otp.verified'));
    }
}



