<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Get all products for farmer
     */
    public function index(Request $request)
    {
        $products = Product::where('seller_id', Auth::id())
            ->with('images')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Create new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity_available' => 'required|integer|min:0',
            'unit' => 'required|string',
            'harvest_id' => 'sometimes|exists:harvests,id',
        ]);

        $validated['seller_id'] = Auth::id();
        $validated['status'] = 'active';

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Get product details
     */
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load('images', 'reviews'),
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'quantity_available' => 'sometimes|integer|min:0',
            'unit' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive,sold_out',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Upload product images
     */
    public function uploadImages(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:2048',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['image_path' => $path]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => $product->load('images'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No images provided',
        ], 400);
    }

    /**
     * Get farmer's sales
     */
    public function sales(Request $request)
    {
        $salesData = \App\Models\OrderItem::whereHas('order', function ($q) {
            $q->whereHas('product', function ($pq) {
                $pq->where('seller_id', Auth::id());
            });
        })
        ->with('product', 'order')
        ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $salesData,
        ]);
    }

    /**
     * Get product statistics
     */
    public function statistics()
    {
        $farmerId = Auth::id();

        $stats = [
            'total_products' => Product::where('seller_id', $farmerId)->count(),
            'active_products' => Product::where('seller_id', $farmerId)->where('status', 'active')->count(),
            'total_sales' => \App\Models\OrderItem::whereHas('product', function ($q) use ($farmerId) {
                $q->where('seller_id', $farmerId);
            })->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
