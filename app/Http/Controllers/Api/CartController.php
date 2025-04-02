<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart_orders_tables;
use App\Models\Product;
use App\Models\User;
use App\Http\Controllers\Api\PayPalController;
class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::where('product_id', $request->product_id)->first();
        if (!$product) {
            return response()->json(['status' => 'error', 'status_code' => 404, 'error_message' => 'Product not found'], 404);
        }

        $price = (float) $product->priceUSD;

        $cart = Cart_orders_tables::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->where('status', 'cart')
            ->first();

        if ($cart) {
            $cart->quantity += $request->quantity;
            $cart->total_price = $cart->quantity * $price;
            $cart->save();
        } else {
            $cart = Cart_orders_tables::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'total_price' => $request->quantity * $price,
                'status' => 'cart'
            ]);
        }

        return response()->json(['status' => 'ok', 'status_code' => 200, 'message' => 'Product added to cart', 'data' => $cart]);
    }

    public function viewCart()
    {
        $cartItems = Cart_orders_tables::with('product')
            ->where('user_id', Auth::id())
            ->where('status', 'cart')
            ->get();

        return response()->json(['status' => 'ok', 'status_code' => 200, 'message' => 'Cart items retrieved', 'data' => $cartItems]);
    }

    public function updateQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart_orders_tables::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->where('status', 'cart')
            ->first();

        if (!$cart) {
            return response()->json(['status' => 'error', 'status_code' => 404, 'error_message' => 'Cart item not found'], 404);
        }

        $product = Product::where('product_id', $request->product_id)->first();
        $cart->quantity = $request->quantity;
        $cart->total_price = $request->quantity * $product->priceUSD;
        $cart->save();

        return response()->json(['status' => 'ok', 'status_code' => 200, 'message' => 'Cart updated', 'data' => $cart]);
    }

    public function removeFromCart($product_id)
    {
        $cart = Cart_orders_tables::where('user_id', Auth::id())
            ->where('product_id', $product_id)
            ->where('status', 'cart')
            ->first();

        if (!$cart) {
            return response()->json(['status' => 'error', 'status_code' => 404, 'error_message' => 'Item not found in cart'], 404);
        }

        $cart->delete();

        return response()->json(['status' => 'ok', 'status_code' => 200, 'message' => 'Item removed from cart']);
    }

    public function placeOrder(Request $request)
    {
        $cartItems = Cart_orders_tables::with('product')
            ->where('user_id', Auth::id())
            ->where('status', 'cart')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => 'error', 'status_code' => 400, 'error_message' => 'Your cart is empty'], 400);
        }

        foreach ($cartItems as $item) {
            $item->status = 'ordered';
            $item->save();
        }

        $purchasedProducts = $cartItems->map(function ($item) {
            return [
                'seller' => $item->product->seller->name ?? null,
                'product_id' => $item->product->product_id,
                'product_name' => $item->product->product_name,
                'product_detail' => $item->product->product_detail,
                'product_claim' => $item->product->product_claim,
                'price_per_unit' => $item->product->priceUSD,
                'quantity' => $item->quantity,
                'total_price' => $item->total_price, 
                'image' => $item->product->image
            ];
        });

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Order placed successfully',
            'products' => $purchasedProducts
        ]);
    }
    public function preparePayPalOrder()
{
    $userId = Auth::id();

    $cartItems = Cart_orders_tables::where('user_id', $userId)->where('status', 'cart')->get();

    if ($cartItems->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'status_code' => 400,
            'error_message' => 'Your cart is empty'
        ], 400);
    }

    // Mark items as pending
    foreach ($cartItems as $item) {
        $item->status = 'pending';
        $item->save();
    }

    return app(PayPalController::class)->payWithPayPal($cartItems->first()->id);
}

    public function orderHistory()
    {
        $orders = Cart_orders_tables::with('product')
            ->where('user_id', Auth::id())
            ->where('status', 'ordered')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $orders->map(function ($item) {
            return [
                'product_name' => $item->product->product_name,
                'product_detail' => $item->product->product_detail,
                'product_claim' => $item->product->product_claim, // ✅ Only visible here
                'price' => $item->product->priceUSD,
                'image' => $item->product->image,
                'ordered_at' => $item->updated_at
            ];
        });

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Order history retrieved',
            'data' => $data
        ]);
    }

    public function adminOrders()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'status_code' => 403,
                'error_message' => 'Only admin can view all orders'
            ], 403);
        }

        $orders = Cart_orders_tables::with(['product', 'user'])
            ->where('status', 'ordered')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'All orders retrieved',
            'data' => $orders
        ]);
    }
}
