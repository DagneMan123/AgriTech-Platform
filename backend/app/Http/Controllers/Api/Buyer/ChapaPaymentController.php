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
            'order_id' => 'nullable',
            'order_ids' => 'nullable|array',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|email',
            'channel' => 'nullable|string',
            'return_url' => 'nullable|string'
        ]);

        $amount = (float)$request->input('amount');
        $orderId = $request->input('order_id');
        $orderIds = $request->input('order_ids', []);
        if ($orderId && empty($orderIds)) {
            $orderIds = [$orderId];
        }
        $primaryOrderId = !empty($orderIds) ? $orderIds[0] : $orderId;
        $txRef = 'AGRI-CHAPA-' . time() . '-' . rand(1000, 9999);
        $channel = $request->input('channel', 'telebirr');

        // Pre-create payment record in database
        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => $primaryOrderId,
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
                'order_ids' => $orderIds,
            ]
        ]);

        // Link orders to transaction reference
        if (!empty($orderIds)) {
            Order::whereIn('id', $orderIds)->update([
                'payment_reference' => $txRef,
                'payment_method' => $channel ?: 'telebirr',
            ]);
        }

        // Register payment method on buyer profile without overriding existing primary method
        $phoneInput = $request->input('phone_number', $user->phone ?? '0911000000');
        $channelTitle = match ($channel) {
            'cbe_birr' => 'CBE Birr (Chapa)',
            'cards', 'card' => 'Chapa Card (Visa / Mastercard)',
            'awash' => 'Awash Birr (Chapa)',
            default => 'Telebirr (Chapa)'
        };
        $methodType = in_array($channel, ['card', 'cards']) ? 'card' : 'mobile_money';

        $hasPrimary = \App\Models\PaymentMethod::where('user_id', $user->id)->where('is_primary', true)->exists();
        \App\Models\PaymentMethod::firstOrCreate(
            [
                'user_id' => $user->id,
                'bank_name' => $channelTitle,
            ],
            [
                'type' => $methodType,
                'account_number' => $phoneInput,
                'account_holder' => $user->name ?: ($user->email ?? 'Account Holder'),
                'is_primary' => !$hasPrimary,
                'is_active' => true,
            ]
        );

        $defaultReturnUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/buyer/orders?tab=active&tx_ref=' . $txRef . '&payment_success=true';
        $returnUrl = $request->input('return_url') ?: $defaultReturnUrl;
        if (!str_contains($returnUrl, 'tx_ref=')) {
            $separator = str_contains($returnUrl, '?') ? '&' : '?';
            $returnUrl .= $separator . 'tx_ref=' . $txRef;
        }

        $names = explode(' ', trim($user->name ?: 'Buyer User'));
        $firstName = $names[0] ?? 'Buyer';
        $lastName = count($names) > 1 ? end($names) : 'User';

        $res = $this->chapa->initializePayment([
            'amount' => $amount,
            'currency' => 'ETB',
            'email' => $request->input('email', $user->email),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $request->input('phone_number', $user->phone),
            'tx_ref' => $txRef,
            'return_url' => $returnUrl,
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
            $payment = Payment::where('transaction_id', $txRef)
                ->orWhere('reference', $txRef)
                ->first();

            if (!$payment) {
                $matchedOrders = Order::where('payment_reference', $txRef)->get();
                if ($matchedOrders->isNotEmpty()) {
                    $payment = Payment::create([
                        'user_id' => $matchedOrders->first()->buyer_id,
                        'order_id' => $matchedOrders->first()->id,
                        'amount' => $matchedOrders->sum('grand_total'),
                        'currency' => 'ETB',
                        'payment_gateway' => 'chapa',
                        'payment_method' => $matchedOrders->first()->payment_method ?: 'telebirr',
                        'transaction_id' => $txRef,
                        'status' => 'completed',
                        'reference' => $txRef,
                        'paid_at' => now(),
                        'completed_at' => now(),
                        'payment_details' => [
                            'order_ids' => $matchedOrders->pluck('id')->toArray(),
                            'verified_at' => now()->toIso8601String()
                        ]
                    ]);
                }
            }

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

                if (!empty($payment->order_id)) {
                    $targetOrderIds = [$payment->order_id];
                } else {
                    $targetOrderIds = [];
                }
                if (!empty($payment->payment_details['order_ids']) && is_array($payment->payment_details['order_ids'])) {
                    $targetOrderIds = array_unique(array_merge($targetOrderIds, $payment->payment_details['order_ids']));
                }

                if (!empty($targetOrderIds)) {
                    Order::whereIn('id', $targetOrderIds)->update([
                        'payment_status' => 'paid',
                        'payment_reference' => $txRef,
                        'status' => 'confirmed'
                    ]);
                }

                // Register payment method on buyer profile without overriding existing primary method
                $channel = $payment->payment_method ?: 'telebirr';
                $channelTitle = match ($channel) {
                    'cbe_birr' => 'CBE Birr (Chapa)',
                    'cards', 'card' => 'Chapa Card (Visa / Mastercard)',
                    'awash' => 'Awash Birr (Chapa)',
                    default => 'Telebirr (Chapa)'
                };
                $phone = $payment->payment_details['phone'] ?? $user?->phone ?? '0911000000';
                $methodType = in_array($channel, ['card', 'cards']) ? 'card' : 'mobile_money';

                $hasPrimary = \App\Models\PaymentMethod::where('user_id', $payment->user_id)->where('is_primary', true)->exists();
                \App\Models\PaymentMethod::firstOrCreate(
                    [
                        'user_id' => $payment->user_id,
                        'bank_name' => $channelTitle,
                    ],
                    [
                        'type' => $methodType,
                        'account_number' => $phone,
                        'account_holder' => $user?->name ?: 'Account Holder',
                        'is_primary' => !$hasPrimary,
                        'is_active' => true
                    ]
                );
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

                    if (!empty($payment->order_id)) {
                        $targetOrderIds = [$payment->order_id];
                    } else {
                        $targetOrderIds = [];
                    }
                    if (!empty($payment->payment_details['order_ids']) && is_array($payment->payment_details['order_ids'])) {
                        $targetOrderIds = array_unique(array_merge($targetOrderIds, $payment->payment_details['order_ids']));
                    }

                    if (!empty($targetOrderIds)) {
                        Order::whereIn('id', $targetOrderIds)->update([
                            'payment_status' => 'paid',
                            'payment_reference' => $txRef,
                            'status' => 'confirmed'
                        ]);
                    }

                    // Register payment method on buyer profile without overriding existing primary method
                    $channel = $payment->payment_method ?: 'telebirr';
                    $channelTitle = match ($channel) {
                        'cbe_birr' => 'CBE Birr (Chapa)',
                        'cards', 'card' => 'Chapa Card (Visa / Mastercard)',
                        'awash' => 'Awash Birr (Chapa)',
                        default => 'Telebirr (Chapa)'
                    };
                    $phone = $payment->payment_details['phone'] ?? '0911000000';
                    $methodType = in_array($channel, ['card', 'cards']) ? 'card' : 'mobile_money';

                    $hasPrimary = \App\Models\PaymentMethod::where('user_id', $payment->user_id)->where('is_primary', true)->exists();
                    \App\Models\PaymentMethod::firstOrCreate(
                        [
                            'user_id' => $payment->user_id,
                            'bank_name' => $channelTitle,
                        ],
                        [
                            'type' => $methodType,
                            'account_number' => $phone,
                            'account_holder' => $payment->user?->name ?: 'Account Holder',
                            'is_primary' => !$hasPrimary,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
