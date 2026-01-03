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

    $transactions = [];
    if ($wallet) {
        // Note: WalletTransaction model not in schema, using DB for now
        $transactions = \Illuminate\Support\Facades\DB::table('wallettransaction')
            ->where('walletAccountID', $wallet->walletAccountID)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

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

        // Fetch Vouchers (if voucher table exists)
        $vouchers = [];
        // Note: Voucher table not in schema, but keeping for future use

        return view('customer.loyalty', compact('card', 'vouchers'));
    }
}