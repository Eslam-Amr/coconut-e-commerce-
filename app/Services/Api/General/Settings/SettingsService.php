<?php

namespace App\Services\Api\General\Settings;

use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class SettingsService
{
    use ApiResponseTrait;
    public function changeLanguage($request)
    {
        try {
            if(Auth::check() || Auth::guard('admin')->check()){
                $user = auth()->user() ?? auth('admin')->user();
                
                if (!$user) {
                    return $this->errorResponse(
                        __('messages.auth.unauthenticated'),
                        401
                    );
                }
                
                $user->update([
                    'locale' => $request['language'],
                ]);
            }
            
            app()->setLocale($request['language']);
            return $this->successNotDataResponse(
                __('messages.settings.language_changed')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                __('messages.settings.language_change_failed'),
                500
            );
        }
    }

    public function toggleNotification()
    {
        try {
            $user = Auth::user() ?? Auth::guard('admin')->user();
            
            if (!$user) {
                return $this->errorResponse(
                    __('messages.auth.unauthenticated'),
                    401
                );
            }
            
            $user->update([
                'notification_status' => !$user->notification_status,
            ]);
            
            return $this->successNotDataResponse(
                __('messages.settings.notification_toggled')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                __('messages.settings.notification_toggle_failed'),
                500
            );
        }
    }

    public function toggleDarkMode()
    {
        try {
            $user = Auth::user() ?? Auth::guard('admin')->user();

            if (!$user) {
                return $this->errorResponse(
                    __('messages.auth.unauthenticated'),
                    401
                );
            }

            $user->update([
                'dark_mode' => !$user->dark_mode,
            ]);

            return $this->successNotDataResponse(
                __('messages.settings.dark_mode_toggled')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                __('messages.settings.dark_mode_toggle_failed'),
                500
            );
        }
    }
}
