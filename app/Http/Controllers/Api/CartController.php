<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart_orders_tables;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * Add a product to the cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
        ]);

        // Get the product price from the `products` table
        $product = Product::where('product_id', $request->product_id)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $productPrice = (float) $product->priceUSD; //  Ensure priceUSD is treated as a float

        $cart = Cart_orders_tables::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->where('status', 'cart')
            ->first();

        if ($cart) {
            //  Update quantity and recalculate total_price
            $cart->quantity += $request->quantity;
            $cart->total_price = $cart->quantity * $productPrice; //  Use actual product price
            $cart->updated_at = now();
            $cart->save();
        } else {
            // Create new cart entry with actual product price
            $cart = Cart_orders_tables::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'total_price' => $request->quantity * $productPrice, //  Correct total price
                'status' => 'cart'
            ]);
        }

        return response()->json(['message' => 'Product added to cart', 'cart' => $cart]);
    }
}

