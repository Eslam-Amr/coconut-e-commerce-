<?php

namespace App\Services\Api\App\Client\Auth\Login;

use App\Models\User;
use App\Notifications\OtpNotification;
use App\Services\Utilities\OtpService;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class LoginService
{
    use ApiResponseTrait;

    public function __construct(public OtpService $otpService) {}
    /**
     * Handle the incoming request.
     */
    public function login($credentials)
    {
        if (!$token = Auth::attempt($credentials))
            return $this->errorResponse(__('messages.login.failed'), code:401);
        return $this->createNewToken($token);
    }

    private function createNewToken($token)
    {
        $user = Auth::user();
        if ($user->email_verified_at == null) {
            return $this->errorResponse(__('messages.login.not_verified'), code:401);
            Auth::logout();
        }
        if (!$user->active) {
            Auth::guard()->logout();
            return $this->errorResponse(__('messages.login.inactive_account'), code:403);
        }
        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => $user
        ], __('messages.login.successful'));
    }

    public function forgetPassword($data)
    {
        $user = User::where('phone', $data['phone'])->first();

        if (!$user) {
            return $this->errorResponse(__('messages.auth.user_not_found'), 404);
        }

        $otp = $this->otpService->generate($data['phone']);

        Notification::route('sms', $user)
            ->notify(new OtpNotification($otp, $user, null, ['sms']));

        return $this->successResponse(null, __('messages.auth.forgot_password.success'));
    }

    public function verifyOtpForgetPassword($data)
    {
        $otp = $this->otpService->verifyForgetPassword($data['phone'], $data['otp']);
        if ($otp['success']) {
            return $this->successResponse($otp, __('messages.auth.otp.verified'));
        }
        return $this->errorResponse($otp, __('messages.auth.otp.invalid'));
    }

    public function resetPassword($data)
    {
        $otpVerification = $this->otpService->isVerifyForgetPassword($data['otp']);
        if (!$otpVerification) {
            return $this->errorResponse($otpVerification, __('messages.auth.otp.invalid'));
        }

        $user = User::where('phone', $data['phone'])->first();
        if (!$user) {
            return $this->errorResponse(__('messages.auth.user_not_found'), 404);
        }

        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        $this->otpService->deleteOldOtps($data['phone']);

        return $this->successResponse(null, __('messages.auth.password_reset.success'));
    }
}
