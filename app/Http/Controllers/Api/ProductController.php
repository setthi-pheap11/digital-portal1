<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Get all products with pagination (Requires authentication)
     */
    public function index()
    {
        $products = Product::with('category', 'seller')->paginate(20);

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * Public API: Get all products for web display (No authentication required)
     */
    public function getPublicProducts()
    {
        $products = Product::with(['category', 'seller'])->get();

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Public product list retrieved successfully',
            'data' => $products
        ], 200);
    }

    /**
     * Store a new product
     */
    public function store(Request $request)
    {
        $seller = $request->user();
    
        if (!$seller || $seller->role !== 'seller') {
            return response()->json([
                'status' => 'error',
                'status_code' => 403,
                'error_code' => 'unauthorized',
                'error_message' => 'Only sellers can add products.'
            ], 403);
        }
    
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_detail' => 'required|string',
            'priceUSD' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'error_code' => 'validation_failed',
                'error_message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create([
            'product_id' => Str::uuid(),
            'product_name' => $request->product_name,
            'product_detail' => $request->product_detail,
            'priceUSD' => $request->priceUSD,
            'category_id' => $request->category_id,
            'seller_id' => $seller->id,
            'image' => $request->image,
        ]);
    
        return response()->json([
            'status' => 'ok',
            'status_code' => 201,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Show a single product (Requires authentication)
     */
    public function show($product_id)
    {
        $product = Product::with('category', 'seller')->where('product_id', $product_id)->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'error_code' => 'not_found',
                'error_message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Product details retrieved successfully',
            'data' => $product
        ], 200);
    }

    /**
     * Update a product
     */
    public function update(Request $request, $product_id)
    {
        $product = Product::where('product_id', $product_id)->first();
    
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'error_code' => 'not_found',
                'error_message' => 'Product not found'
            ], 404);
        }
    
        // Ensure the authenticated user is the seller of the product
        $seller = $request->user();
        if (!$seller || $seller->role !== 'seller' || $seller->id !== $product->seller_id) {
            return response()->json([
                'status' => 'error',
                'status_code' => 403,
                'error_code' => 'unauthorized',
                'error_message' => 'Unauthorized. Only the product owner can update it.'
            ], 403);
        }
    
        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|string|max:255',
            'product_detail' => 'sometimes|string',
            'priceUSD' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'image' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'error_code' => 'validation_failed',
                'error_message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
    
        $product->update($request->all());
    
        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Product updated successfully',
            'data' => $product
        ], 200);
    }

    /**
     * Delete a product
     */
    public function destroy(Request $request, $product_id)
    {
        $product = Product::where('product_id', $product_id)->first();
    
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'error_code' => 'not_found',
                'error_message' => 'Product not found'
            ], 404);
        }
    
        // Ensure the authenticated user is the seller
        $seller = $request->user();
        if (!$seller || $seller->role !== 'seller' || $seller->id !== $product->seller_id) {
            return response()->json([
                'status' => 'error',
                'status_code' => 403,
                'error_code' => 'unauthorized',
                'error_message' => 'Unauthorized. Only the product owner can delete it.'
            ], 403);
        }
    
        $product->delete();
    
        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
