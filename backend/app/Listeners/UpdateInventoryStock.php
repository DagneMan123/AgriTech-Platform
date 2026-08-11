<?php

namespace App\Listeners;

use App\Events\OrderPlaced;

class UpdateInventoryStock
{
    public function handle(OrderPlaced $event)
    {
        foreach ($event->order->items as $item) {
            $item->product->decrement('available_quantity', $item->quantity);
        }
    }
}
