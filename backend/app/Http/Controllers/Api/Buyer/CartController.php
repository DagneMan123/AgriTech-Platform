<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get current user's cart
     */
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())
            ->with('items.product')
            ->firstOrCreate(['user_id' => Auth::id()]);

        return response()->json([
            'success' => true,
            'data' => $cart->load('items.product'),
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Check stock availability
        if ($product->quantity_available < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available',
            ], 422);
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $cartItem = $cart->items()
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $cartItem->quantity + $validated['quantity'],
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => $cart->load('items.product'),
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = $cartItem->product;

        if ($product->quantity_available < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available',
            ], 422);
        }

        $cartItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => $cartItem,
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(CartItem $cartItem)
    {
        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }

    /**
     * Get cart totals
     */
    public function totals()
    {
        $cart = Cart::where('user_id', Auth::id())
            ->with('items')
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => true,
                'data' => [
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'items_count' => 0,
                ],
            ]);
        }

        $subtotal = $cart->items->sum(fn ($item) => $item->price * $item->quantity);
        $tax = $subtotal * 0.1; // 10% tax
        $total = $subtotal + $tax;

        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'items_count' => $cart->items->count(),
            ],
        ]);
    }
}
