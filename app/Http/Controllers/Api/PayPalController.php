<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart_orders_tables;
use PayPal\Api\{Amount, Payer, Payment, RedirectUrls, Transaction};
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Illuminate\Support\Facades\Auth;

class PayPalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(new OAuthTokenCredential(
            env('PAYPAL_CLIENT_ID'),
            env('PAYPAL_SECRET')
        ));
        $this->apiContext->setConfig(['mode' => env('PAYPAL_MODE')]);
    }

    public function payWithPayPal($orderId)
    {
        $order = Cart_orders_tables::where('id', $orderId)->where('user_id', Auth::id())->where('status', 'pending')->firstOrFail();
        
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal($order->total_price)->setCurrency('USD');

        $transaction = new Transaction();
        $transaction->setAmount($amount)->setDescription("Order Payment");

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(url('/api/paypal/success/' . $order->id))
                     ->setCancelUrl(url('/api/paypal/cancel'));

        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions([$transaction]);

        $payment->create($this->apiContext);
        return response()->json(['paypal_url' => $payment->getApprovalLink()]);
    }

    public function paypalSuccess(Request $request, $orderId)
    {
        $order = Cart_orders_tables::where('id', $orderId)->where('user_id', Auth::id())->where('status', 'pending')->firstOrFail();
        
        $order->update(['status' => 'paid', 'payment_id' => $request->paymentId]);
        
        return response()->json(['message' => 'Payment successful', 'order' => $order]);
    }
}
