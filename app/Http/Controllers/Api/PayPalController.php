<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart_orders_tables;
use App\Models\Product;
use PayPal\Api\{Amount, Payer, Payment, RedirectUrls, Transaction};
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Illuminate\Support\Facades\Auth;

class PayPalController extends Controller
{
    private $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(new OAuthTokenCredential(
            env('PAYPAL_CLIENT_ID'),
            env('PAYPAL_SECRET')
        ));
        $this->apiContext->setConfig([
            'mode' => env('PAYPAL_MODE', 'sandbox'),
        ]);
    }

    /**
     * Step 1: Generate PayPal Payment Link
     */
    public function payWithPayPal($orderId)
    {
        $order = Cart_orders_tables::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();
    
        try {
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
    
            $amount = new Amount();
            $amount->setCurrency('USD');
            $amount->setTotal(number_format((float) $order->total_price, 2, '.', ''));
    
            $transaction = new Transaction(); // ⚠️ Don't overwrite this name!
            $transaction->setAmount($amount);
            $transaction->setDescription("Payment for order ID #{$order->id}");
    
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(url("/api/paypal/success/{$order->id}"));
            $redirectUrls->setCancelUrl(url("/api/paypal/cancel"));
    
            $payment = new Payment();
            $payment->setIntent('sale'); // ✅ This stays a string
            $payment->setPayer($payer);
            $payment->setRedirectUrls($redirectUrls);
            $payment->setTransactions([$transaction]); // ✅ Must be array of Transaction object
    
            $payment->create($this->apiContext);
    
            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() === 'approval_url') {
                    return response()->json([
                        'status' => 'ok',
                        'status_code' => 200,
                        'paypal_url' => $link->getHref(),
                    ]);
                }
            }
    
            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'error_message' => 'PayPal approval URL not found.',
            ], 500);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'error_message' => 'Failed to create PayPal payment.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    

    /**
     * Step 2: PayPal Payment Success Callback
     */
    public function paypalSuccess(Request $request, $orderId)
    {
        $userId = Auth::id();

        $pendingItems = Cart_orders_tables::with('product.seller')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->where('id', $orderId)
            ->get();

        if ($pendingItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'error_message' => 'No pending orders found.'
            ], 404);
        }

        foreach ($pendingItems as $item) {
            $item->update([
                'status' => 'ordered',
                'payment_id' => $request->paymentId,
            ]);
        }

        $products = $pendingItems->map(function ($item) {
            return [
                'seller' => $item->product->seller->name ?? null,
                'product_id' => $item->product->product_id,
                'product_name' => $item->product->product_name,
                'product_detail' => $item->product->product_detail,
                'product_claim' => $item->product->product_claim, // show only on success
                'price_per_unit' => $item->product->priceUSD,
                'quantity' => $item->quantity,
                'total_price' => $item->total_price,
                'image' => $item->product->image,
            ];
        });

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Payment successful and order placed.',
            'products' => $products,
        ]);
    }
}
