<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class PublicCategoryController extends Controller
{
    /**
     * Get all categories with product count
     */
    public function index()
    {
        $categories = Category::withCount('products')->get();

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ], 200);
    }

    /**
     * Get all products inside a category
     */
    public function getProductsByCategory($category_id)
    {
        $category = Category::with(['products' => function ($query) {
            $query->select('product_id', 'product_name', 'product_detail', 'priceUSD', 'category_id', 'seller_id', 'image');
        }])->find($category_id);
    
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'error_code' => 'not_found',
                'error_message' => 'Category not found'
            ], 404);
        }
    
        // Use IMAGE_BASE_URL from .env or default to local storage
        $imageBaseUrl = config('app.image_url');
    
        // Modify product image URLs
        $products = $category->products->map(function ($product) use ($imageBaseUrl) {
            if ($product->image) {
                $product->image = $imageBaseUrl . '/' . ltrim($product->image, '/');
            } else {
                $product->image = null;
            }
            return $product;
        });
    
        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Products retrieved successfully',
            'category' => $category->name,
            'data' => $products
        ], 200);
    }
    
    
}
