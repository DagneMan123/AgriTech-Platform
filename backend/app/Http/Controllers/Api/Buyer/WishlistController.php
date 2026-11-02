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
        $userId = Auth::id();
        $wishlist = Wishlist::where('user_id', $userId)
            ->with(['product.farmer'])
            ->get();

        $data = $wishlist->map(function ($item) {
            $product = $item->product ?: Product::find($item->product_id);
            if (!$product) return null;

            $images = $product->images;
            $imageUrl = null;
            if (!empty($images) && is_array($images) && count($images) > 0) {
                $img = $images[0];
                $imageUrl = str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
            }

            $farmerName = 'Regional Supplier';
            if ($product->farmer) {
                $farmerName = $product->farmer->farm_name ?: ($product->farmer->user->name ?? 'Regional Supplier');
            }

            return [
                'id' => $item->id,
                'wishlist_id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float)$product->price,
                'price_unit' => $product->price_unit ?: 'ETB/kg',
                'unit' => $product->quantity_unit ?: ($product->unit ?: 'kg'),
                'quantity' => (float)($product->quantity ?? 0),
                'quantity_available' => (float)($product->quantity_available ?? $product->quantity ?? 0),
                'total_stock_value' => (float)$product->price * (float)($product->quantity ?? 0),
                'in_stock' => ($product->quantity > 0) && in_array($product->status, ['active', 'available', 'approved']),
                'image' => $imageUrl ?: $product->image,
                'farmer_name' => $farmerName,
                'category' => $product->category,
                'created_at' => $item->created_at,
                'product' => $product,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $data,
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
    public function destroy($id)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('product_id', $id);
            })
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
    public function moveToCart(Request $request, $id)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('product_id', $id);
            })
            ->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist',
            ], 404);
        }

        $product = Product::find($wishlist->product_id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
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
