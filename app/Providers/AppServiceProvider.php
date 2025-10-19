<?php

namespace App\Providers;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Admin;
use App\Models\Media;
use App\Models\User;
use App\Notifications\Channels\EmailChannel;
use App\Notifications\Channels\SmsChannel;
use App\Observers\AdminObserver;
use App\Observers\MediaObserver;
use App\Observers\UserObserver;
use App\Services\Utilities\StripePaymentService;
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

        $this->app->bind(PaymentGatewayInterface::class,StripePaymentService::class);

        // Register observers
        User::observe(UserObserver::class);

        Admin::observe(AdminObserver::class);
        
        Media::observe(MediaObserver::class);

        Notification::extend('email', function ($app) {
            return new EmailChannel;
        });
        
        Notification::extend('sms', function ($app) {
            return new SmsChannel;
        });

        
    }
}
