<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart_orders_tables;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function placeOrder()
    {
        $cartItems = Cart_orders_tables::where('user_id', Auth::id())
            ->where('status', 'cart')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $totalPrice = 0;

        foreach ($cartItems as $item) {
            $totalPrice += $item->quantity * 2.00; // Replace with actual price retrieval
        }

        // ✅ Fix: Ensure `total_price` is updated properly
        Cart_orders_tables::where('user_id', Auth::id())
            ->where('status', 'cart')
            ->update([
                'status' => 'pending',
                'total_price' => $totalPrice, 
                'updated_at' => now()
            ]);

        // ✅ Fetch the updated orders
        $order = Cart_orders_tables::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => $order // ✅ Now returns the order details
        ]);
    }
}
