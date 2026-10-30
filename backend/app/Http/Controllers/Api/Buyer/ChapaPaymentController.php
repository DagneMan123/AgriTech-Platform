<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Services\ChapaService;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;

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
            'email' => 'nullable|email'
        ]);

        $amount = (float)$request->input('amount');
        $orderId = $request->input('order_id');
        $txRef = 'AGRI-CHAPA-' . time() . '-' . rand(1000, 9999);

        // Pre-create payment record in database
        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_gateway' => 'chapa',
            'payment_method' => 'chapa_digital',
            'transaction_id' => $txRef,
            'status' => 'pending',
            'reference' => 'chapa_ref_' . time(),
        ]);

        $res = $this->chapa->initializePayment([
            'amount' => $amount,
            'currency' => 'ETB',
            'email' => $request->input('email', $user->email ?? 'buyer@agriconnect.et'),
            'first_name' => $user->name ? explode(' ', $user->name)[0] : 'Buyer',
            'last_name' => $user->name && count(explode(' ', $user->name)) > 1 ? explode(' ', $user->name)[1] : 'Account',
            'phone_number' => $request->input('phone_number', $user->phone ?? '0911000000'),
            'tx_ref' => $txRef
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chapa checkout initialized successfully',
            'data' => [
                'tx_ref' => $txRef,
                'checkout_url' => $res['checkout_url'] ?? null,
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
                    'reference' => $txRef
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
}
