<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
class CustomerDashboardController extends Controller
{
   public function wallet()
{
    $userID = Auth::id();
    $customer = Customer::where('userID', $userID)->first();
    
    $balance = 0.00;

    if ($customer) {
        // Fetch the Wallet Balance
        $wallet = DB::table('walletaccount')
            ->where('customerID', $customer->customerID)
            ->first();
        
        if ($wallet) {
            $balance = $wallet->wallet_balance; 
        }
    }

    // Make sure this matches your view folder structure: 'customer.wallet' based on your error log
    return view('customer.wallet', compact('balance'));
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