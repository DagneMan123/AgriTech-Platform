<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class UpdateOrderStatus extends Command
{
    protected $signature = 'orders:update-status';
    protected $description = 'Update order statuses based on delivery dates';

    public function handle()
    {
        // Auto-complete orders that have been delivered
        Order::where('status', '!=', 'delivered')
            ->where('expected_delivery_date', '<', now())
            ->update(['status' => 'delivered', 'delivered_at' => now()]);

        $this->info('Order statuses updated successfully');
    }
}
