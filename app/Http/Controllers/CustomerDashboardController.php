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
        $customer = \App\Models\Customer::where('userID', $user->userID)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Profile not found.');
        }

        $wallet = $customer->walletAccount;

        // Just pass the raw value
        $outstanding = $wallet ? $wallet->outstanding_amount : 0.00;

        // FIXED: Removed the database query to 'wallettransaction'
        // We set this to an empty array [] so the view doesn't crash.
        $transactions = [];

        return view('customer.wallet', compact('outstanding', 'transactions'));
    }

    public function loyalty()
    {
        $user = Auth::user();
        $customer = \App\Models\Customer::where('userID', $user->userID)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Customer profile not found.');
        }

        // Fetch Loyalty Card
        $card = $customer->loyaltyCard;

        // Fetch Vouchers
        $vouchers = [];
        // You can uncomment this if you want to show vouchers later:
        // $vouchers = \App\Models\Voucher::where('customerID', $customer->customerID)->get();

        return view('customer.loyalty', compact('card', 'vouchers'));
    }
}
