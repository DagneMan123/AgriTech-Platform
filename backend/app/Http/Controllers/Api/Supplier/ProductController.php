<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Get supplier products
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
     * Create product
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
            'sku' => 'sometimes|string|unique:products',
            'minimum_order' => 'sometimes|integer|min:1',
            'max_delivery_days' => 'sometimes|integer|min:1',
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
        $this->authorize('view', $product);

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
            'status' => 'sometimes|in:active,inactive,discontinued',
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
     * Bulk update products status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'status' => 'required|in:active,inactive,discontinued',
        ]);

        Product::whereIn('id', $validated['product_ids'])
            ->where('seller_id', Auth::id())
            ->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Products updated successfully',
        ]);
    }

    /**
     * Get product statistics
     */
    public function statistics()
    {
        $supplierId = Auth::id();

        $stats = [
            'total_products' => Product::where('seller_id', $supplierId)->count(),
            'active_products' => Product::where('seller_id', $supplierId)->where('status', 'active')->count(),
            'total_sales' => \App\Models\OrderItem::whereHas('product', function ($q) use ($supplierId) {
                $q->where('seller_id', $supplierId);
            })->count(),
            'average_rating' => Product::where('seller_id', $supplierId)
                ->with('reviews')
                ->get()
                ->map(fn ($p) => $p->reviews->avg('rating'))
                ->average(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
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
}
