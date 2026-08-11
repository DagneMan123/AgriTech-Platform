<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Charge;

class PaymentController extends Controller
{
    /**
     * Get payment methods
     */
    public function paymentMethods()
    {
        $methods = PaymentMethod::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    /**
     * Add payment method
     */
    public function addPaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'card_number' => 'required|string',
            'card_holder' => 'required|string',
            'expiry_month' => 'required|integer|min:1|max:12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cvv' => 'required|string|size:3,4',
            'is_default' => 'sometimes|boolean',
        ]);

        $paymentMethod = PaymentMethod::create([
            'user_id' => Auth::id(),
            ...$validated,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method added successfully',
            'data' => $paymentMethod,
        ], 201);
    }

    /**
     * Process payment for order
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'payment_method_id' => 'required_if:payment_gateway,stripe',
            'payment_gateway' => 'required|in:stripe,paypal,bank_transfer',
            'amount' => 'required|numeric|min:0',
        ]);

        $buyerId = Auth::id();
        $orders = Order::whereIn('id', $validated['order_ids'])
            ->where('buyer_id', $buyerId)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid orders found',
            ], 422);
        }

        try {
            $payment = $this->processPaymentGateway(
                $validated['payment_gateway'],
                $validated['amount'],
                $validated,
                $buyerId
            );

            if (!$payment['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $payment['message'],
                ], 422);
            }

            // Create payment record
            $paymentRecord = Payment::create([
                'user_id' => $buyerId,
                'amount' => $validated['amount'],
                'payment_gateway' => $validated['payment_gateway'],
                'transaction_id' => $payment['transaction_id'],
                'status' => 'completed',
                'reference' => $payment['reference'],
            ]);

            // Update orders
            foreach ($orders as $order) {
                $order->update([
                    'payment_id' => $paymentRecord->id,
                    'status' => 'confirmed',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment_id' => $paymentRecord->id,
                    'transaction_id' => $payment['transaction_id'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process payment with specific gateway
     */
    private function processPaymentGateway(string $gateway, float $amount, array $data, int $buyerId): array
    {
        return match ($gateway) {
            'stripe' => $this->processStripePayment($amount, $data),
            'paypal' => $this->processPaypalPayment($amount, $data),
            'bank_transfer' => $this->processBankTransfer($amount, $data, $buyerId),
            default => [
                'success' => false,
                'message' => 'Unsupported payment gateway',
            ],
        };
    }

    /**
     * Process Stripe payment
     */
    private function processStripePayment(float $amount, array $data): array
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $charge = Charge::create([
                'amount' => (int)($amount * 100),
                'currency' => 'usd',
                'source' => $data['payment_method_id'],
                'description' => 'AgriConnect Order Payment',
            ]);

            return [
                'success' => true,
                'transaction_id' => $charge->id,
                'reference' => $charge->balance_transaction,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process PayPal payment
     */
    private function processPaypalPayment(float $amount, array $data): array
    {
        // Implement PayPal integration
        return [
            'success' => true,
            'transaction_id' => 'PAYPAL_' . uniqid(),
            'reference' => 'paypal_ref_' . time(),
        ];
    }

    /**
     * Process bank transfer
     */
    private function processBankTransfer(float $amount, array $data, int $buyerId): array
    {
        return [
            'success' => true,
            'transaction_id' => 'BANK_' . uniqid(),
            'reference' => 'bank_ref_' . time(),
        ];
    }

    /**
     * Get payment history
     */
    public function history(Request $request)
    {
        $payments = Payment::where('user_id', Auth::id())
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Refund payment
     */
    public function refund(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'reason' => 'required|string',
            'amount' => 'sometimes|numeric|min:0|max:' . $payment->amount,
        ]);

        $refundAmount = $validated['amount'] ?? $payment->amount;

        try {
            $refund = \App\Models\Refund::create([
                'payment_id' => $payment->id,
                'amount' => $refundAmount,
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund requested successfully',
                'data' => $refund,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund request failed',
            ], 500);
        }
    }
}
