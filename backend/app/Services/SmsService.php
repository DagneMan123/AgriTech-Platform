<?php

namespace App\Services;

class SmsService
{
    public function send(string $phone, string $message): bool
    {
        // Integration point for SMS gateway (Twilio, Nexmo, etc.)
        logger()->info("SMS to $phone: $message");
        return true;
    }

    public function sendOrderConfirmation($order)
    {
        $message = "Your order #{$order->order_number} has been confirmed. Total: {$order->total_amount}";
        return $this->send($order->buyer->phone, $message);
    }

    public function sendDeliveryNotification($delivery)
    {
        $message = "Your delivery #{$delivery->delivery_number} is on the way";
        return $this->send($delivery->buyer->phone, $message);
    }
}
