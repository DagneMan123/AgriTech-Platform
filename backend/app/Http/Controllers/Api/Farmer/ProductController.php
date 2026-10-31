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
        $farmer = $this->getOrCreateFarmer();
        $farmerId = $farmer ? $farmer->id : Auth::id();

        $query = Product::where('farmer_id', $farmerId)
            ->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $limit = $request->get('limit', 50);
        $products = $query->paginate($limit);

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
        $farmer = $this->getOrCreateFarmer();
        if (!$farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer profile not found.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity_available' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'quantity_unit' => 'nullable|string|max:50',
            'quality_grade' => 'nullable|string|max:50',
            'harvest_id' => 'nullable',
            'status' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:5120',
        ]);

        $quantity = $request->filled('quantity_available')
            ? (float) $request->input('quantity_available')
            : (float) ($request->input('quantity') ?? 0);

        $unit = $request->input('unit') ?? $request->input('quantity_unit') ?? 'kg';
        $status = $request->input('status') ?? 'active';

        $images = [];
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $images[] = $path;
        }

        $harvestId = $request->filled('harvest_id') && is_numeric($request->input('harvest_id'))
            ? (int) $request->input('harvest_id')
            : null;

        $product = Product::create([
            'farmer_id' => $farmer->id,
            'crop_id' => $harvestId,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'price_unit' => 'ETB/' . $unit,
            'quantity' => $quantity,
            'quantity_unit' => $unit,
            'quality_grade' => $validated['quality_grade'] ?? null,
            'status' => $status,
            'images' => $images,
        ]);

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
            'data' => $product->loadMissing('farmer', 'crop'),
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $farmer = $this->getOrCreateFarmer();
        if ($farmer && $product->farmer_id !== $farmer->id && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity_available' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'quantity_unit' => 'nullable|string|max:50',
            'quality_grade' => 'nullable|string|max:50',
            'harvest_id' => 'nullable',
            'status' => 'sometimes|string|max:50',
            'image' => 'nullable|image|max:5120',
        ]);

        $updateData = [];
        if ($request->has('name')) $updateData['name'] = $request->name;
        if ($request->has('description')) $updateData['description'] = $request->description;
        if ($request->has('category')) $updateData['category'] = $request->category;
        if ($request->has('price')) $updateData['price'] = $request->price;
        if ($request->has('status')) $updateData['status'] = $request->status;
        if ($request->has('quality_grade')) $updateData['quality_grade'] = $request->quality_grade;
        if ($request->has('harvest_id')) {
            $updateData['crop_id'] = is_numeric($request->harvest_id) ? (int)$request->harvest_id : null;
        }

        if ($request->has('quantity_available')) {
            $updateData['quantity'] = (float)$request->quantity_available;
        } elseif ($request->has('quantity')) {
            $updateData['quantity'] = (float)$request->quantity;
        }

        if ($request->has('unit')) {
            $updateData['quantity_unit'] = $request->unit;
            $updateData['price_unit'] = 'ETB/' . $request->unit;
        } elseif ($request->has('quantity_unit')) {
            $updateData['quantity_unit'] = $request->quantity_unit;
        }

        if ($request->hasFile('image')) {
            if (!empty($product->images) && is_array($product->images)) {
                foreach ($product->images as $oldImg) {
                    if (Storage::disk('public')->exists($oldImg)) {
                        Storage::disk('public')->delete($oldImg);
                    }
                }
            }
            $path = $request->file('image')->store('products', 'public');
            $updateData['images'] = [$path];
        }

        $product->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->fresh(),
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        $farmer = $this->getOrCreateFarmer();
        if ($farmer && $product->farmer_id !== $farmer->id && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        if (!empty($product->images) && is_array($product->images)) {
            foreach ($product->images as $oldImg) {
                if (Storage::disk('public')->exists($oldImg)) {
                    Storage::disk('public')->delete($oldImg);
                }
            }
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
        $farmer = $this->getOrCreateFarmer();
        if ($farmer && $product->farmer_id !== $farmer->id && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:5120',
        ]);

        if ($request->hasFile('images')) {
            $currentImages = is_array($product->images) ? $product->images : [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $currentImages[] = $path;
            }
            $product->update(['images' => $currentImages]);

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => $product->fresh(),
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
        $farmer = $this->getOrCreateFarmer();
        $farmerId = $farmer ? $farmer->id : Auth::id();

        $salesData = \App\Models\OrderItem::whereHas('product', function ($pq) use ($farmerId) {
            $pq->where('farmer_id', $farmerId);
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
        $farmer = $this->getOrCreateFarmer();
        $farmerId = $farmer ? $farmer->id : Auth::id();

        $stats = [
            'total_products' => Product::where('farmer_id', $farmerId)->count(),
            'active_products' => Product::where('farmer_id', $farmerId)->where('status', 'active')->count(),
            'total_sales' => \App\Models\OrderItem::whereHas('product', function ($q) use ($farmerId) {
                $q->where('farmer_id', $farmerId);
            })->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
