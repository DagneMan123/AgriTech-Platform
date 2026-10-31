<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Services\ChapaService;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChapaPaymentController extends Controller
{
    protected ChapaService $chapa;

    public function __construct(ChapaService $chapa)
    {
        $this->chapa = $chapa;
    }

    /**
     * Initialize a produce or escrow payment session with Chapa.
     */
    public function initialize(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'order_id' => 'nullable|exists:orders,id',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|email',
            'channel' => 'nullable|string'
        ]);

        $amount = (float)$request->input('amount');
        $orderId = $request->input('order_id');
        $txRef = 'AGRI-CHAPA-' . time() . '-' . rand(1000, 9999);
        $channel = $request->input('channel', 'telebirr');

        // Pre-create payment record in database
        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => 'ETB',
            'payment_gateway' => 'chapa',
            'payment_method' => $channel ?: 'telebirr',
            'transaction_id' => $txRef,
            'status' => 'pending',
            'reference' => 'chapa_ref_' . time(),
            'payment_details' => [
                'channel' => $channel,
                'phone' => $request->input('phone_number', $user->phone),
                'email' => $request->input('email', $user->email),
            ]
        ]);

        $res = $this->chapa->initializePayment([
            'amount' => $amount,
            'currency' => 'ETB',
            'email' => $request->input('email', $user->email ?? 'aydenfudagne@gmail.com'),
            'first_name' => $user->name ? explode(' ', $user->name)[0] : 'Ayden',
            'last_name' => $user->name && count(explode(' ', $user->name)) > 1 ? explode(' ', $user->name)[1] : 'Buyer',
            'phone_number' => $request->input('phone_number', $user->phone ?? '0911000000'),
            'tx_ref' => $txRef
        ]);

        if (empty($res['success']) || empty($res['checkout_url'])) {
            $payment->update([
                'status' => 'failed',
                'error_message' => $res['message'] ?? 'Chapa initialization failed'
            ]);
            return response()->json([
                'success' => false,
                'message' => $res['message'] ?? 'Failed to initialize payment with Chapa.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chapa checkout initialized successfully',
            'data' => [
                'tx_ref' => $txRef,
                'checkout_url' => $res['checkout_url'],
                'amount' => $amount,
                'currency' => 'ETB',
                'payment_id' => $payment->id
            ]
        ]);
    }

    /**
     * Verify transaction and mark payment complete in database.
     */
    public function verify(Request $request, $txRef)
    {
        $user = $request->user();

        $verification = $this->chapa->verifyPayment($txRef);

        if ($verification['success']) {
            $payment = Payment::where('transaction_id', $txRef)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'reference' => $txRef,
                    'paid_at' => now(),
                    'completed_at' => now(),
                    'payment_details' => array_merge($payment->payment_details ?? [], [
                        'verified_at' => now()->toIso8601String(),
                        'verification_data' => $verification['data'] ?? []
                    ])
                ]);

                if ($payment->order_id) {
                    Order::where('id', $payment->order_id)->update([
                        'payment_id' => $payment->id,
                        'payment_status' => 'paid',
                        'status' => 'confirmed'
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Chapa payment confirmed and verified in database!',
                'data' => $verification['data'] ?? []
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment verification pending or failed.'
        ], 400);
    }

    /**
     * Get Chapa supported institutions list.
     */
    public function banks()
    {
        return response()->json([
            'success' => true,
            'data' => $this->chapa->getSupportedBanks()
        ]);
    }

    /**
     * Webhook/Callback handler called by Chapa IPN.
     */
    public function callback(Request $request)
    {
        Log::info('Chapa webhook callback received: ' . json_encode($request->all()));
        $txRef = $request->input('trx_ref') ?? $request->input('tx_ref');

        if ($txRef) {
            $payment = Payment::where('transaction_id', $txRef)->first();
            if ($payment) {
                $verification = $this->chapa->verifyPayment($txRef);
                if ($verification['success'] ?? false) {
                    $payment->update([
                        'status' => 'completed',
                        'paid_at' => now(),
                        'completed_at' => now(),
                        'payment_details' => array_merge($payment->payment_details ?? [], [
                            'webhook_data' => $request->all(),
                            'verified_via_webhook' => true
                        ])
                    ]);

                    if ($payment->order_id) {
                        Order::where('id', $payment->order_id)->update([
                            'payment_id' => $payment->id,
                            'payment_status' => 'paid',
                            'status' => 'confirmed'
                        ]);
                    }
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
