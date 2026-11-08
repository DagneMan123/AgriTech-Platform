<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ShippingCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get current user's cart items
     */
    public function index(Request $request)
    {
        $userId = Auth::id() ?? ($request->user() ? $request->user()->id : null);

        $cartItems = Cart::where('user_id', $userId)
            ->with(['product.farmer.user'])
            ->get();

        $formatted = $cartItems->map(function ($item) {
            $product = $item->product;
            $unitPrice = $product ? (float) $product->price : 0;
            $qty = (int) $item->quantity;
            $subtotal = $unitPrice * $qty;

            $supplierName = '';
            if ($product) {
                $supplierName = $product->farmer_name ?: ($product->farmer?->cooperative_name ?: ($product->farmer?->farm_name ?: ($product->farmer?->user?->name ?? '')));
            }

            return [
                'id' => $item->id,
                'cart_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $product ? $product->name : 'Unknown Product',
                'supplier_name' => $supplierName,
                'unit_price' => $unitPrice,
                'price' => $unitPrice,
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'image_url' => $product ? $product->image : null,
                'sku' => 'SKU-' . $item->product_id,
                'unit' => $product ? ($product->unit ?? $product->quantity_unit ?? 'kg') : 'kg',
                'origin_location' => $product ? ($product->origin_location ?: 'Addis Ababa') : 'Addis Ababa',
                'product' => $product,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'total_items' => $formatted->sum('quantity'),
            'subtotal' => $formatted->sum('subtotal'),
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $userId = Auth::id() ?? ($request->user() ? $request->user()->id : null);
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        $productId = $validated['product_id'];
        $quantity = isset($validated['quantity']) ? (int) $validated['quantity'] : 1;

        $product = Product::with(['farmer.user'])->findOrFail($productId);

        // Check stock availability if tracked
        $availableQty = (float) ($product->quantity_available ?? $product->quantity ?? 0);
        if ($availableQty > 0 && $availableQty < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Only {$availableQty} {$product->unit} available in stock",
            ], 422);
        }

        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        $supplierName = '';
        if ($product) {
            $supplierName = $product->farmer_name ?: ($product->farmer?->cooperative_name ?: ($product->farmer?->farm_name ?: ($product->farmer?->user?->name ?? '')));
        }

        $formatted = [
            'id' => $cartItem->id,
            'cart_id' => $cartItem->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'supplier_name' => $supplierName,
            'unit_price' => (float) $product->price,
            'price' => (float) $product->price,
            'quantity' => (int) $cartItem->quantity,
            'subtotal' => (float) ($product->price * $cartItem->quantity),
            'image_url' => $product->image,
            'sku' => 'SKU-' . $product->id,
            'unit' => $product->unit ?? $product->quantity_unit ?? 'kg',
            'product' => $product,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'data' => $formatted,
        ], 201);
    }

    /**
     * Alias for store method
     */
    public function addItem(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = Auth::id() ?? ($request->user() ? $request->user()->id : null);
        $cartItem = Cart::where('user_id', $userId)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            $cartItem = Cart::where('user_id', $userId)
                ->where('product_id', $id)
                ->firstOrFail();
        }

        $product = Product::findOrFail($cartItem->product_id);
        $availableQty = (float) ($product->quantity_available ?? $product->quantity ?? 0);
        if ($availableQty > 0 && $availableQty < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "Only {$availableQty} {$product->unit} available in stock",
            ], 422);
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => [
                'id' => $cartItem->id,
                'quantity' => $cartItem->quantity,
                'subtotal' => (float) ($product->price * $cartItem->quantity),
            ],
        ]);
    }

    /**
     * Alias for update method
     */
    public function updateItem(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    /**
     * Remove item from cart
     */
    public function destroy($id)
    {
        $userId = Auth::id() ?? auth()->id();
        $cartItem = Cart::where('user_id', $userId)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('product_id', $id);
            })
            ->first();

        if ($cartItem) {
            $cartItem->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }

    /**
     * Alias for destroy method
     */
    public function removeItem($id)
    {
        return $this->destroy($id);
    }

    /**
     * Clear all items from current user's cart
     */
    public function clear()
    {
        $userId = Auth::id() ?? auth()->id();
        Cart::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }

    /**
     * Checkout current cart items
     */
    public function checkout(Request $request)
    {
        $userId = Auth::id() ?? auth()->id();
        $user = Auth::user();

        $cartItems = Cart::where('user_id', $userId)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 400);
        }

        $subtotal = $cartItems->sum(function ($item) {
            return (float) ($item->product ? $item->product->price * $item->quantity : 0);
        });

        // Dynamic Multi-Origin Shipping Matrix Calculation based on product origins & buyer destination
        $rawRegion = $request->input('delivery_region', 'Addis Ababa');
        $region = str_contains($rawRegion, ',') ? trim(explode(',', $rawRegion)[1]) : $rawRegion;
        $shippingType = $request->input('shipping_type', 'standard');
        $multiOriginBreakdown = ShippingCalculatorService::getMultiOriginBreakdown($cartItems, $region, $shippingType);
        $totalWeightKg = $multiOriginBreakdown['total_weight_kg'];
        $serverCalculatedFee = $multiOriginBreakdown['total_fee'];

        $clientDeliveryFee = (float) $request->input('delivery_fee', 0);
        $deliveryFee = $clientDeliveryFee > 0 ? $clientDeliveryFee : $serverCalculatedFee;
        $grandTotal = $subtotal + $deliveryFee;

        // Group by farmer so each farmer gets an order
        $grouped = $cartItems->groupBy(function ($item) {
            return $item->product ? $item->product->farmer_id : 1;
        });

        $createdOrders = [];
        $numFarmers = $grouped->count();

        DB::beginTransaction();
        try {
            foreach ($grouped as $farmerId => $items) {
                $groupSubtotal = $items->sum(fn ($i) => (float) ($i->product ? $i->product->price * $i->quantity : 0));

                // Allocate delivery fee proportionally if multi-farmer cart
                $groupWeight = ShippingCalculatorService::calculateTotalWeightKg($items);
                $groupDeliveryFee = $numFarmers > 1
                    ? round(($groupWeight / max(0.01, $totalWeightKg)) * $deliveryFee, 2)
                    : $deliveryFee;

                $tax = (float) $request->input('tax_amount', $request->input('tax', 0));
                $discount = (float) $request->input('promo_discount', $request->input('discount', 0));
                $orderGrandTotal = max(0, $groupSubtotal + $groupDeliveryFee + $tax - $discount);

                $deliveryAddress = $request->input('delivery_address');
                if (empty($deliveryAddress)) {
                    $parts = array_filter([
                        $request->input('delivery_destination'),
                        $request->input('delivery_woreda'),
                        $request->input('delivery_zone'),
                        $request->input('delivery_region'),
                        'Ethiopia'
                    ]);
                    $deliveryAddress = !empty($parts) ? implode(', ', array_unique($parts)) : ($user->address ?? 'Address on file');
                }

                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                    'buyer_id' => $userId,
                    'farmer_id' => $farmerId,
                    'subtotal' => $groupSubtotal,
                    'delivery_fee' => $groupDeliveryFee,
                    'tax' => $tax,
                    'discount' => $discount,
                    'grand_total' => $orderGrandTotal,
                    'status' => 'pending',
                    'delivery_address' => $deliveryAddress,
                    'delivery_region' => $request->input('delivery_region', $user->region ?? ''),
                    'delivery_zone' => $request->input('delivery_zone', $user->zone ?? ''),
                    'delivery_woreda' => $request->input('delivery_woreda', $user->woreda ?? ''),
                    'delivery_phone' => $request->input('phone', $request->input('delivery_phone', $user->phone ?? '')),
                    'payment_method' => $request->input('payment_method', 'telebirr'),
                    'payment_status' => 'pending',
                    'escrow_status' => 'held',
                    'escrow_held_at' => now(),
                    'payment_reference' => $request->input('payment_reference', 'REF-' . strtoupper(Str::random(10))),
                    'notes' => $request->input('notes', $request->input('delivery_instructions')),
                ]);

                foreach ($items as $item) {
                    if ($item->product) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'price' => $item->product->price,
                            'subtotal' => $item->product->price * $item->quantity,
                        ]);

                        // Reduce product quantity available if positive
                        if ($item->product->quantity >= $item->quantity) {
                            $item->product->decrement('quantity', $item->quantity);
                        }
                    }
                }

                $createdOrders[] = $order;
            }

            // Clear cart
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            $paymentMethod = strtolower($request->input('payment_method', 'telebirr'));
            $isChapa = !in_array($paymentMethod, ['cash_on_delivery', 'cod']);
            $checkoutUrl = null;
            $txRef = null;

            if ($isChapa && !empty($createdOrders)) {
                try {
                    $totalAmount = array_sum(array_map(fn ($o) => (float) $o->grand_total, $createdOrders));
                    $primaryOrder = $createdOrders[0];
                    $allOrderIds = array_map(fn ($o) => $o->id, $createdOrders);
                    $txRef = 'AGRI-CHAPA-' . time() . '-' . rand(1000, 9999);

                    // Pre-create payment record in database with neutral escrow custody
                    $payment = \App\Models\Payment::create([
                        'user_id' => $userId,
                        'order_id' => $primaryOrder->id,
                        'amount' => $totalAmount,
                        'currency' => 'ETB',
                        'payment_gateway' => 'chapa',
                        'payment_method' => $paymentMethod,
                        'transaction_id' => $txRef,
                        'status' => 'pending',
                        'reference' => 'chapa_ref_' . time(),
                        'escrow_status' => 'held',
                        'escrow_held_at' => now(),
                        'escrow_custody_account' => 'AgriConnect & Chapa Agricultural Escrow Custody Account',
                        'payment_details' => [
                            'channel' => $paymentMethod,
                            'phone' => $request->input('phone', $user->phone ?? '0911000000'),
                            'email' => $user->email,
                            'order_ids' => $allOrderIds,
                        ]
                    ]);

                    foreach ($createdOrders as $o) {
                        $o->update([
                            'payment_reference' => $txRef,
                            'payment_method' => $paymentMethod,
                            'escrow_status' => 'held',
                            'escrow_held_at' => now(),
                        ]);
                    }

                    $chapaService = app(\App\Services\ChapaService::class);
                    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                    $returnUrl = $request->input('return_url') ?: ($frontendUrl . '/buyer/orders?tab=active&tx_ref=' . $txRef . '&payment_success=true&escrow_locked=true');
                    if (!str_contains($returnUrl, 'tx_ref=')) {
                        $separator = str_contains($returnUrl, '?') ? '&' : '?';
                        $returnUrl .= $separator . 'tx_ref=' . $txRef . '&payment_success=true&escrow_locked=true';
                    }

                    $names = explode(' ', trim($user->name ?: 'Buyer User'));
                    $firstName = $names[0] ?? 'Buyer';
                    $lastName = count($names) > 1 ? end($names) : 'User';

                    $chapaRes = $chapaService->initializePayment([
                        'amount' => $totalAmount,
                        'currency' => 'ETB',
                        'email' => $user->email ?? 'aydenfudagne@gmail.com',
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone_number' => $request->input('phone', $user->phone ?? '0911000000'),
                        'tx_ref' => $txRef,
                        'return_url' => $returnUrl,
                    ]);

                    if (!empty($chapaRes['success']) && !empty($chapaRes['checkout_url'])) {
                        $checkoutUrl = $chapaRes['checkout_url'];
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Chapa auto-init on checkout failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => $createdOrders,
                'orders' => $createdOrders,
                'checkout_url' => $checkoutUrl,
                'tx_ref' => $txRef,
                'is_chapa' => $isChapa,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to checkout: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dynamic shipping calculation estimate for the current cart
     */
    public function shippingEstimate(Request $request)
    {
        $userId = Auth::id() ?? ($request->user() ? $request->user()->id : null);
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();
        $totalWeight = ShippingCalculatorService::calculateTotalWeightKg($cartItems);
        $region = $request->query('region', 'Addis Ababa');

        $hasPerishables = ShippingCalculatorService::hasPerishables($cartItems);
        $options = ShippingCalculatorService::getDynamicShippingOptions($cartItems, $region);

        return response()->json([
            'success' => true,
            'origin' => $originInfo['origin'],
            'destination' => $region,
            'distance_km' => $originInfo['distance_km'],
            'total_weight_kg' => round($totalWeight, 2),
            'has_perishables' => $hasPerishables,
            'region' => $region,
            'options' => $options,
        ]);
    }
}
