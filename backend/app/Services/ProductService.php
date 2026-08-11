<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function createProduct(array $data)
    {
        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data)
    {
        return $product->update($data);
    }

    public function deleteProduct(Product $product)
    {
        Storage::disk('public')->deleteDirectory('products/' . $product->id);
        return $product->delete();
    }

    public function getProductsByFarmer($farmerId)
    {
        return Product::where('farmer_id', $farmerId)->paginate(20);
    }

    public function searchProducts(string $query)
    {
        return Product::where('name', 'like', "%$query%")
            ->orWhere('description', 'like', "%$query%")
            ->paginate(20);
    }
}
