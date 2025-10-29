<?php

namespace App\Observers;

use App\Models\MoneyTransfer;
use App\Models\Admin;
use App\Notifications\MoneyTransferCreatedNotification;
use App\Notifications\MoneyTransferStatusChangedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MoneyTransferObserver
{
    /**
     * Handle the MoneyTransfer "created" event.
     */
    public function created(MoneyTransfer $moneyTransfer): void
    {
        // Notify admins: real-time (redis) + database if notification_status is true, otherwise database only
        $superAdmins = Admin::whereHas('role', function ($query) {
            $query->where('name', 'super_admin');
        })->get();
        // $admins = Admin::query()->get();
        // if ($admins->isEmpty()) {
        //     return;
        // }
        if ($superAdmins->isEmpty()) 
            return;
        

        $superAdminsWithRealtime = $superAdmins->where('notification', true);
        $superAdminsDatabaseOnly = $superAdmins->where('notification', false);

        if ($superAdminsWithRealtime->isNotEmpty()) {
            Notification::send($superAdminsWithRealtime, new MoneyTransferCreatedNotification($moneyTransfer, ['database','redis']));
        }

        if ($superAdminsDatabaseOnly->isNotEmpty()) {
            Notification::send($superAdminsDatabaseOnly, new MoneyTransferCreatedNotification($moneyTransfer, ['database']));
        }


        
    }

    /**
     * Handle the MoneyTransfer "updated" event.
     */
    public function updated(MoneyTransfer $moneyTransfer): void
    {
        try {
            if ($moneyTransfer->wasChanged('status')) {
                // Only proceed if the actor is an admin (avoid firing for user self-cancel)
                if (!Auth::guard('admin')->check()) {
                    return;
                }

                $oldStatus = $moneyTransfer->getOriginal('status');
                $newStatus = $moneyTransfer->status;

                // Load user if not loaded
                $moneyTransfer->loadMissing('user');
                if ($moneyTransfer->user) {
                    $channels = ['database'];
                    if ($moneyTransfer->user->notification) {
                        $channels[] = 'redis';
                    }

                    $moneyTransfer->user->notify(new MoneyTransferStatusChangedNotification(
                        $moneyTransfer,
                        $oldStatus,
                        $newStatus,
                        $channels
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify user about money transfer status change', [
                'transfer_id' => $moneyTransfer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the MoneyTransfer "deleted" event.
     */
    public function deleted(MoneyTransfer $moneyTransfer): void
    {
        //
    }

    /**
     * Handle the MoneyTransfer "restored" event.
     */
    public function restored(MoneyTransfer $moneyTransfer): void
    {
        //
    }

    /**
     * Handle the MoneyTransfer "force deleted" event.
     */
    public function forceDeleted(MoneyTransfer $moneyTransfer): void
    {
        //
    }
}
