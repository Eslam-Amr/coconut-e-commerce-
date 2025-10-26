<?php

namespace App\Providers;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Admin;
use App\Models\Media;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use App\Notifications\Channels\EmailChannel;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\RedisChannel;
use App\Observers\AdminObserver;
use App\Observers\MediaObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductReviewObserver;
use App\Observers\ProductVariantObserver;
use App\Observers\UserObserver;
use App\Observers\WishlistObserver;
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

        // Interaction tracking observers
        Wishlist::observe(WishlistObserver::class);
        ProductReview::observe(ProductReviewObserver::class);
        Order::observe(OrderObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);

        Notification::extend('email', function ($app) {
            return new EmailChannel;
        });
        
        Notification::extend('sms', function ($app) {
            return new SmsChannel;
        });

        Notification::extend('redis', function ($app) {
            return new RedisChannel;
        });

        
    }
}
