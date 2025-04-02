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
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 20); // Default to 20 if not specified
    
        $products = Product::with('category', 'seller')->paginate($perPage);
    
        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * Public API: Get all products for web display with pagination (No authentication required)
     */
    public function getPublicProducts(Request $request)
    {
        $perPage = $request->query('per_page', 20); // Default to 20
    
        $imageBaseUrl = config('app.image_url', url('storage'));
    
        $products = Product::with(['category', 'seller'])->paginate($perPage);
    
        $products->getCollection()->transform(function ($product) use ($imageBaseUrl) {
            return [
                'id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_detail' => $product->product_detail,
                'product_claim' => $product->product_claim,
                'priceUSD' => $product->priceUSD,
                'category' => $product->category->name ?? null,
                'seller' => $product->seller->name ?? null,
                'image_url' => $product->image ? $imageBaseUrl . '/' . ltrim($product->image, '/') : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });
    
        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Public product list retrieved successfully',
            'data' => $products
        ]);
    }
    

    /**
     * Public API: Get product detail by ID (No authentication required)
     */
    public function getPublicProductDetail($product_id)
    {
        $imageBaseUrl = config('app.image_url', url('storage'));

        $product = Product::with(['category', 'seller'])
            ->where('product_id', $product_id)
            ->first();

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
            'data' => [
                'id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_detail' => $product->product_detail,
                'product_claim' => $product->product_claim,
                'priceUSD' => $product->priceUSD,
                'category' => $product->category->name ?? null,
                'seller' => $product->seller->name ?? null,
                'image_url' => $product->image ? $imageBaseUrl . '/' . ltrim($product->image, '/') : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]
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
            'product_claim' => 'required|string',
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
            'product_claim' => $request->product_claim,
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
        return $this->getPublicProductDetail($product_id);
    }
}
