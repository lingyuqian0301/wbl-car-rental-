<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $filterRating = $request->get('filter_rating');
        $filterDateFrom = $request->get('filter_date_from');
        $filterDateTo = $request->get('filter_date_to');

        $query = Review::with(['booking.customer.user', 'booking.vehicle']);

        // Search by booking ID, customer name, or plate number
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                  ->orWhereHas('booking.customer.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('booking.vehicle', function($vQuery) use ($search) {
                      $vQuery->where('plate_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by rating
        if ($filterRating !== null && $filterRating !== '') {
            $query->where('rating', $filterRating);
        }

        // Filter by date range
        if ($filterDateFrom) {
            $query->whereDate('review_date', '>=', $filterDateFrom);
        }
        if ($filterDateTo) {
            $query->whereDate('review_date', '<=', $filterDateTo);
        }

        $reviews = $query->orderBy('review_date', 'desc')
                        ->orderBy('reviewID', 'desc')
                        ->paginate(20)->withQueryString();

        // Calculate statistics
        $today = Carbon::today();
        $totalReviews = Review::count();
        $averageRating = Review::avg('rating') ?? 0;
        $reviewsToday = Review::whereDate('review_date', $today)->count();
        $reviewsThisMonth = Review::whereMonth('review_date', $today->month)
                                  ->whereYear('review_date', $today->year)
                                  ->count();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'search' => $search,
            'filterRating' => $filterRating,
            'filterDateFrom' => $filterDateFrom,
            'filterDateTo' => $filterDateTo,
            'totalReviews' => $totalReviews,
            'averageRating' => round($averageRating, 1),
            'reviewsToday' => $reviewsToday,
            'reviewsThisMonth' => $reviewsThisMonth,
            'today' => $today,
        ]);
    }

    /**
     * Get review by booking ID (API endpoint)
     */
    public function getByBookingId(Request $request)
    {
        $bookingId = $request->get('booking_id');
        
        if (!$bookingId) {
            return response()->json(['error' => 'Booking ID is required'], 400);
        }

        try {
            $review = Review::where('bookingID', $bookingId)
                ->with(['booking.customer.user', 'booking.vehicle'])
                ->first();

            if ($review) {
                return response()->json([
                    'review' => [
                        'reviewID' => $review->reviewID,
                        'bookingID' => $review->bookingID,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'review_date' => $review->review_date ? $review->review_date->format('Y-m-d') : null,
                    ]
                ]);
            } else {
                return response()->json(['review' => null]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch review: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch review'], 500);
        }
    }
}
