<?php

namespace App\Providers;

use App\Models\Booking;
use App\Observers\BookingObserver;
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
        // Register Booking Observer for Keep Deposit logic
        Booking::observe(BookingObserver::class);

        // Register Blade directive for file URLs
        \Blade::directive('fileUrl', function ($expression) {
            return "<?php echo getFileUrl($expression); ?>";
        });
    }
}
