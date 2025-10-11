<?php

namespace App\Http\Controllers\Api\General\Notification;

use App\Http\Controllers\Controller;
use App\Services\Api\General\Notification\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class NotificationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['authenticated'];
    }
    public function __construct(public NotificationService $notificationService) {}

    /**
     * List all notifications for the authenticated user
     */
    public function list()
    {
        return $this->notificationService->list();
    }

    /**
     * Mark a specific notification as read
     */
    public function read($id)
    {
        return $this->notificationService->read($id);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        return $this->notificationService->markAllAsRead();
    }

    /**
     * Delete a specific notification
     */
    public function delete($id)
    {
        return $this->notificationService->delete($id);
    }
}
