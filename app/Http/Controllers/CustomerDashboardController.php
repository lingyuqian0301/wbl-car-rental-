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
    $customer = \Illuminate\Support\Facades\DB::table('customer')->where('user_id', $user->id)->first();

    if (!$customer) {
        return redirect()->route('home')->with('error', 'Profile not found.');
    }

    $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')->where('customerID', $customer->customerID)->first();
    
    // Just pass the raw value
    $outstanding = $wallet ? $wallet->outstanding_amount : 0.00;

    $transactions = [];
    if ($wallet) {
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