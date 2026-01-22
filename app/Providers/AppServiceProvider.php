<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Payment;
use App\Observers\BookingObserver;
use App\Observers\PaymentObserver;
use Illuminate\Pagination\Paginator;
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
        // Register Booking Observer for notifications and Keep Deposit logic
        Booking::observe(BookingObserver::class);
        
        // Register Payment Observer for notifications
        Payment::observe(PaymentObserver::class);

        // Use Bootstrap pagination views to match the admin UI styling
        Paginator::useBootstrapFive();

        // Register Blade directive for file URLs
        \Blade::directive('fileUrl', function ($expression) {
            return "<?php echo getFileUrl($expression); ?>";
        });
    }
}
