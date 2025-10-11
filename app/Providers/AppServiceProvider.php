<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\User;
use App\Notifications\Channels\EmailChannel;
use App\Notifications\Channels\SmsChannel;
use App\Observers\MediaObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        User::observe(UserObserver::class);

        Media::observe(MediaObserver::class);

        Notification::extend('email', function ($app) {
            return new EmailChannel;
        });
        
        Notification::extend('sms', function ($app) {
            return new SmsChannel;
        });

        
    }
}
