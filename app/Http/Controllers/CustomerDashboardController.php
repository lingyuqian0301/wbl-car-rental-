<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
   public function wallet()
    {
        $user = Auth::user();
        $customer = DB::table('customer')->where('user_id', $user->id)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Customer profile not found.');
        }

        // 1. Calculate Outstanding Dynamically (Cost - Payments)
        $bookings = DB::table('booking')
                    ->where('customerID', $customer->customerID)
                    ->where('booking_status', '!=', 'Cancelled')
                    ->get();
        
        $totalCost = $bookings->sum('total_amount');
        $bookingIds = $bookings->pluck('bookingID');
        
        $totalPaid = DB::table('payment')
                    ->whereIn('bookingID', $bookingIds)
                    ->where('status', 'Verified')
                    ->sum('amount');

        $outstanding = max(0, $totalCost - $totalPaid);

        // 2. Fetch Transactions (Using 'payment' table directly as history)
        // Since you calculate dynamically, your Payment history IS your wallet history.
        $transactions = DB::table('payment')
            ->join('booking', 'payment.bookingID', '=', 'booking.bookingID')
            ->where('booking.customerID', $customer->customerID)
            ->select('payment.*', 'booking.bookingID', 'booking.total_amount as booking_total')
            ->orderBy('payment.payment_date', 'desc')
            ->get();

        return view('customer.wallet', compact('outstanding', 'transactions'));
    }

    public function loyalty()
    {
        $user = Auth::user();
        $customer = DB::table('customer')->where('user_id', $user->id)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Customer profile not found.');
        }

        // Fetch Loyalty Card
        $card = DB::table('loyaltycard')->where('customerID', $customer->customerID)->first();

        // Fetch Vouchers
        $vouchers = [];
        if ($card) {
            $vouchers = DB::table('voucher') 
                ->where('loyaltyCardID', $card->loyaltyCardID)
                ->get();
        }

        return view('customer.loyalty', compact('card', 'vouchers'));
    }
}