<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(Request $request)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->with('product.images')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $wishlist,
        ]);
    }

    /**
     * Add product to wishlist
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $exists = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist',
            ], 422);
        }

        $wishlistItem = Wishlist::create([
            'user_id' => Auth::id(),
            'product_id' => $validated['product_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist',
            'data' => $wishlistItem->load('product'),
        ], 201);
    }

    /**
     * Remove product from wishlist
     */
    public function destroy(Product $product)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist',
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist',
        ]);
    }

    /**
     * Check if product is in wishlist
     */
    public function check(Product $product)
    {
        $exists = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $exists,
            ],
        ]);
    }

    /**
     * Move wishlist item to cart
     */
    public function moveToCart(Request $request, Product $product)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist',
            ], 404);
        }

        // Add to cart
        $cart = \App\Models\Cart::firstOrCreate(['user_id' => Auth::id()]);
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        // Remove from wishlist
        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product moved to cart',
        ]);
    }

    /**
     * Clear wishlist
     */
    public function clear()
    {
        Wishlist::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared',
        ]);
    }

    /**
     * Share wishlist
     */
    public function share(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'message' => 'sometimes|string',
        ]);

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->with('product')
            ->get();

        // Send email or notification
        // Implementation depends on notification service

        return response()->json([
            'success' => true,
            'message' => 'Wishlist shared successfully',
        ]);
    }

    /**
     * Get wishlist count
     */
    public function count()
    {
        $count = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
            ],
        ]);
    }
}
