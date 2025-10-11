<?php

namespace App\Services\Api\General\Profile;

use App\Http\Resources\Api\General\Profile\ProfileResource;
use App\Models\User;
use App\Notifications\OtpNotification;
use App\Services\Utilities\OtpService;

use App\Services\Utilities\MediaService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class ProfileService
{
    use ApiResponseTrait;

    public function __construct(
        private OtpService $otpService,
        private MediaService $mediaService
    ) {}

    /**
     * Change user password
     */
    public function changePassword($request)
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        // Check if current password is correct
        if (!Hash::check($request['current_password'], $user->password)) {
            return $this->errorResponse(__('messages.settings.incorrect_current_password'), [], 400);
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($request['new_password'])
        ]);
        
        return $this->successNotDataResponse(
            __('messages.settings.password_changed')
        );
    }

    /**
     * Request phone number change (send OTP)
     */
    public function requestPhoneChange($request)
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        // Generate OTP for the new phone number
        $otp = $this->otpService->generate($request['new_phone']);
        
        // Send OTP notification
        Notification::route('sms', $request['new_phone'])
            ->notify(new OtpNotification($otp, $user, $request['new_phone'], ['sms']));
        
        return $this->successNotDataResponse(
            __('messages.settings.phone_change_requested')
        );
    }

    /**
     * Verify OTP and change phone number
     */
    public function verifyPhoneOtp($request)
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        // Verify OTP
        $otpVerification = $this->otpService->verifyForgetPassword($request['new_phone'], $request['otp']);
        
        if (!$otpVerification['success']) {
            return $this->errorResponse(__('messages.auth.otp.invalid'), [], 400);
        }
        
        // Update phone number
        $user->update([
            'phone' => $request['new_phone']
        ]);
        
        // Delete the used OTP
        $this->otpService->deleteOldOtps($request['new_phone']);
        
        return $this->successNotDataResponse(
            __('messages.settings.phone_changed')
        );
    }

    /**
     * Delete user account
     */
    public function deleteAccount($request)
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        // Validate password confirmation
        if (!Hash::check($request['password'], $user->password)) {
            return $this->errorResponse(__('messages.settings.incorrect_password_for_deletion'), [], 400);
        }
        
        // Delete user account
        $user->delete();
        
        // Logout user
        Auth::logout();
        
        return $this->successNotDataResponse(
            __('messages.settings.account_deleted')
        );
    }
    /**
     * Update user profile (name, email, and image)
     */
    public function updateProfile($request)
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        // Handle image upload if provided
        if (isset($request['image']) && $request['image'] instanceof UploadedFile) {
            // Delete existing profile images
            $this->mediaService->deleteAllMediaForModel($user, 'image');
            
            // Store new image
            $media = $this->mediaService->storeMedia($request['image'], $user, 'users');
            
            // Remove image from request data as it's handled separately
            unset($request['image']);
        }
        
        // Update user profile data
        $user->update($request);
        
        // Load media relationship for response
        $user->load('media');
        
        return $this->successResponse(
            new ProfileResource($user),
            __('messages.settings.profile_updated')
        );
    }

    public function profileData(){
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        // Load media relationship
        $user->load('media');
        
        return $this->successResponse(
            new ProfileResource($user),
            __('messages.settings.profile_data_retrieved')
        );      
    }   
    
}
