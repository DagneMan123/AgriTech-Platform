<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Order;

class PaymentService
{
    public function processPayment(array $data)
    {
        $order = Order::findOrFail($data['order_id']);

        $payment = Payment::create([
            'payment_number' => 'PAY-' . random_int(100000, 999999),
            'order_id' => $order->id,
            'payer_id' => auth()->id(),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'] ?? null,
            'status' => 'pending',
        ]);

        if ($data['payment_method'] !== 'cash_on_delivery') {
            $payment->update(['status' => 'completed', 'completed_at' => now()]);
            $order->update(['payment_status' => 'paid']);

            Transaction::create([
                'payment_id' => $payment->id,
                'user_id' => auth()->id(),
                'amount' => $data['amount'],
                'type' => 'debit',
                'description' => "Payment for order #{$order->order_number}",
            ]);
        }

        return $payment;
    }

    public function verifyPayment(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            throw new \Exception('Payment already processed');
        }

        $payment->update(['status' => 'completed', 'completed_at' => now()]);
        $payment->order->update(['payment_status' => 'paid']);

        return $payment;
    }
}
