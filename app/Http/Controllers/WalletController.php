<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class WalletController extends Controller
{
    public function show()
    {
        $userID = Auth::id();
        $customer = Customer::where('userID', $userID)->first();
        
        $balance = 0.00;

        if ($customer) {
            // Fetch the Wallet Record
            $wallet = DB::table('walletaccount')
                ->where('customerID', $customer->customerID)
                ->first();
            
            // We use 'wallet_balance' (Credit) instead of 'outstanding_amount' (Debt)
            if ($wallet) {
                $balance = $wallet->wallet_balance; 
            }
        }

        return view('wallet', compact('balance'));
    }
}