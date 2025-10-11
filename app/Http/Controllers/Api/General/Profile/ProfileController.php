<?php

namespace App\Http\Controllers\Api\General\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\General\Profile\ChangePasswordRequest;
use App\Http\Requests\Api\General\Profile\ChangePhoneRequest;
use App\Http\Requests\Api\General\Profile\DeleteAccountRequest;
use App\Http\Requests\Api\General\Profile\UpdateProfileRequest;
use App\Http\Requests\Api\General\Profile\VerifyPhoneOtpRequest;
use App\Services\Api\General\Profile\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['authenticated'];
    }
    public function __construct(public ProfileService $profileService) {}

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->profileService->changePassword($request->validated());
    }

    public function requestPhoneChange(ChangePhoneRequest $request)
    {
        return $this->profileService->requestPhoneChange($request->validated());
    }

    public function verifyPhoneOtp(VerifyPhoneOtpRequest $request)
    {
        return $this->profileService->verifyPhoneOtp($request->validated());
    }

    public function deleteAccount(DeleteAccountRequest $request)
    {
        return $this->profileService->deleteAccount($request->validated());
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->profileService->updateProfile($request->validated());
    }

    public function profileData()
    {
        return $this->profileService->profileData();
    }
}
