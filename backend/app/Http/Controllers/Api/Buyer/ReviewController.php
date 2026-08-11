<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Get product reviews
     */
    public function index(Request $request, Product $product)
    {
        $reviews = Review::where('product_id', $product->id)
            ->where('approved', true)
            ->with('user')
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Create review
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|min:10',
        ]);

        // Check if user has purchased the product
        $hasPurchased = Order::where('buyer_id', Auth::id())
            ->whereHas('items', function ($q) use ($validated) {
                $q->where('product_id', $validated['product_id']);
            })
            ->where('status', 'delivered')
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review products you have purchased',
            ], 422);
        }

        // Check if already reviewed
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product',
            ], 422);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            ...$validated,
            'approved' => false, // Moderate before displaying
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. It will be displayed after moderation.',
            'data' => $review,
        ], 201);
    }

    /**
     * Update review
     */
    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'sometimes|string|max:255',
            'comment' => 'sometimes|string|min:10',
        ]);

        $review->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review,
        ]);
    }

    /**
     * Delete review
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Get user's reviews
     */
    public function myReviews(Request $request)
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with('product')
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Get product rating summary
     */
    public function ratingSummary(Product $product)
    {
        $reviews = Review::where('product_id', $product->id)
            ->where('approved', true)
            ->get();

        $ratingCounts = $reviews->groupBy('rating')->map->count();
        $averageRating = $reviews->count() > 0 ? $reviews->avg('rating') : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $reviews->count(),
                'rating_distribution' => [
                    '5_stars' => $ratingCounts[5] ?? 0,
                    '4_stars' => $ratingCounts[4] ?? 0,
                    '3_stars' => $ratingCounts[3] ?? 0,
                    '2_stars' => $ratingCounts[2] ?? 0,
                    '1_stars' => $ratingCounts[1] ?? 0,
                ],
            ],
        ]);
    }

    /**
     * Like review
     */
    public function like(Review $review)
    {
        $like = $review->likes()->where('user_id', Auth::id())->first();

        if ($like) {
            $like->delete();
            return response()->json([
                'success' => true,
                'data' => [
                    'liked' => false,
                    'likes_count' => $review->likes()->count(),
                ],
            ]);
        }

        $review->likes()->create(['user_id' => Auth::id()]);

        return response()->json([
            'success' => true,
            'data' => [
                'liked' => true,
                'likes_count' => $review->likes()->count(),
            ],
        ]);
    }

    /**
     * Report review
     */
    public function report(Request $request, Review $review)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'description' => 'sometimes|string',
        ]);

        \App\Models\ReviewReport::create([
            'review_id' => $review->id,
            'reported_by' => Auth::id(),
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review reported successfully',
        ]);
    }
}
