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

        // Fetch active vouchers for this customer
        $vouchers = [];
        if ($card) {
            $vouchers = DB::table('voucher')
                ->where('loyaltyCardID', $card->loyaltyCardID)
                ->where('voucher_isActive', 1)
                ->get();
        }

        return view('customer.loyalty', compact('card', 'vouchers'));
    }

    /**
     * Claim discount - deduct 5 stamps and create voucher
     */
    public function claimDiscount()
    {
        $user = Auth::user();
        $customer = \App\Models\Customer::where('userID', $user->userID)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Customer profile not found.');
        }

        // Fetch Loyalty Card
        $loyaltyCard = DB::table('loyaltycard')
            ->where('customerID', $customer->customerID)
            ->first();

        if (!$loyaltyCard) {
            return redirect()->route('home')->with('error', 'Loyalty card not found.');
        }

        // Check if customer has enough stamps
        if ($loyaltyCard->total_stamps < 5) {
            return redirect()->route('home')->with('error', 'Not enough stamps. You need 5 stamps to claim a discount.');
        }

        // Check if customer already has an active voucher
        $existingVoucher = DB::table('voucher')
            ->where('loyaltyCardID', $loyaltyCard->loyaltyCardID)
            ->where('voucher_isActive', 1)
            ->first();

        if ($existingVoucher) {
            return redirect()->route('home')->with('warning', 'You already have an active discount voucher. Use it on your next booking!');
        }

        // Deduct 5 stamps
        DB::table('loyaltycard')
            ->where('loyaltyCardID', $loyaltyCard->loyaltyCardID)
            ->update([
                'total_stamps' => $loyaltyCard->total_stamps - 5,
                'loyalty_last_updated' => now()
            ]);

        // Create voucher (10% discount)
        DB::table('voucher')->insert([
            'loyaltyCardID' => $loyaltyCard->loyaltyCardID,
            'discount_type' => 'PERCENT',
            'discount_amount' => 10,
            'voucher_isActive' => 1,
        ]);

        return redirect()->route('home')->with('success', '🎉 Congratulations! You claimed a 10% discount voucher! It will be automatically applied to your next booking.');
    }
}