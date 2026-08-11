<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\SmsService;

class SendOrderSms
{
    public function __construct(private SmsService $smsService)
    {}

    public function handle(OrderPlaced $event)
    {
        $farmers = $event->order->items()->distinct('farmer_id')->pluck('farmer_id');
        
        foreach ($farmers as $farmerId) {
            $farmer = \App\Models\User::find($farmerId);
            if ($farmer?->phone) {
                $this->smsService->send(
                    $farmer->phone,
                    "You have received a new order #{$event->order->order_number}"
                );
            }
        }
    }
}
