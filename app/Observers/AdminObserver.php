<?php

namespace App\Observers;

use App\Models\Admin;

class AdminObserver
{
    public function creating(Admin $admin): void
    {
        $admin->user_type = 'admin';
        $admin->email_verified_at = now();
        if ($admin && !$admin->role_id) {
            $admin->assignRole('viewer');
        }

    }
    public function created(Admin $admin): void
    {
        if ($admin && !$admin->role_id) {
            $admin->assignRole('viewer');
        }
    }
}
