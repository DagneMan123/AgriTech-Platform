<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Support\Str;

class OrderService
{
    public function createOrder(array $data)
    {
        $totalAmount = 0;

        $order = Order::create([
            'order_number' => 'ORD-' . Str::random(10),
            'buyer_id' => auth()->id(),
            'delivery_address' => $data['delivery_address'],
            'delivery_region' => $data['delivery_region'],
            'delivery_zone' => $data['delivery_zone'],
            'delivery_woreda' => $data['delivery_woreda'],
            'delivery_latitude' => $data['delivery_latitude'],
            'delivery_longitude' => $data['delivery_longitude'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        foreach ($data['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];
            $totalAmount += $itemTotal;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'farmer_id' => $product->farmer_id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $itemTotal,
            ]);

            $product->decrement('available_quantity', $item['quantity']);
        }

        $tax = $totalAmount * 0.1;
        $order->update([
            'subtotal' => $totalAmount,
            'tax' => $tax,
            'total_amount' => $totalAmount + $tax,
        ]);

        $this->notifyFarmers($order);

        return $order;
    }

    public function updateOrderStatus(Order $order, string $status, array $data = [])
    {
        $order->update(['status' => $status]);

        if ($status === 'confirmed') {
            $order->update(['expected_delivery_date' => now()->addDays($data['preparation_time_days'] ?? 3)]);
        }

        if ($status === 'rejected' && isset($data['rejection_reason'])) {
            $order->update(['rejection_reason' => $data['rejection_reason']]);
            foreach ($order->items as $item) {
                $item->product->increment('available_quantity', $item->quantity);
            }
        }

        return $order;
    }

    private function notifyFarmers(Order $order)
    {
        $farmers = $order->items()->distinct('farmer_id')->pluck('farmer_id');
        foreach ($farmers as $farmerId) {
            Notification::create([
                'user_id' => $farmerId,
                'title' => 'New Order',
                'message' => "You have received a new order #{$order->order_number}",
                'type' => 'order',
                'related_id' => $order->id,
            ]);
        }
    }
}
