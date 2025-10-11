<?php

namespace App\Services\Api\General\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    use ApiResponseTrait;

    /**
     * List all notifications for the authenticated user
     */
    public function list()
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        if (!$user) {
            return $this->errorResponse(
                __('messages.auth.unauthenticated'),
                401
            );
        }
        
        // Use Laravel's built-in notification relationship
        $notifications = $user->notifications()
            ->unread()
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return $this->successResponse($notifications, __('messages.notifications.retrieved'));
    }

    /**
     * Mark a specific notification as read
     */
    public function read($notificationId)
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        if (!$user) {
            return $this->errorResponse(
                __('messages.auth.unauthenticated'),
                401
            );
        }
        
        // Use Laravel's built-in notification relationship with UUID support
        $notification = $user->notifications()->where('id', $notificationId)->first();
        
        if (!$notification) {
            return $this->errorResponse(__('messages.notifications.not_found'), [], 404);
        }
        
        if ($notification->read_at) {
            return $this->errorResponse(__('messages.notifications.already_read'), [], 400);
        }
        
        $notification->markAsRead();
        
        return $this->successResponse($notification, __('messages.notifications.marked_as_read'));
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        if (!$user) {
            return $this->errorResponse(
                __('messages.auth.unauthenticated'),
                401
            );
        }
        
        // Use Laravel's built-in notification relationship
        $updatedCount = $user->unreadNotifications()->update(['read_at' => now()]);
        
        return $this->successNotDataResponse(
            __('messages.notifications.all_marked_as_read', ['count' => $updatedCount])
        );
    }

    /**
     * Delete a specific notification
     */
    public function delete($notificationId)
    {
        /** @var User $user */
        $user = Auth::user() ?? Auth::guard('admin')->user();
        
        if (!$user) {
            return $this->errorResponse(
                __('messages.auth.unauthenticated'),
                401
            );
        }
        
        // Use Laravel's built-in notification relationship with UUID support
        $notification = $user->notifications()->where('id', $notificationId)->first();
        
        if (!$notification) {
            return $this->errorResponse(__('messages.notifications.not_found'), [], 404);
        }
        
        $notification->delete();
        
        return $this->successNotDataResponse(__('messages.notifications.deleted'));
    }
}
