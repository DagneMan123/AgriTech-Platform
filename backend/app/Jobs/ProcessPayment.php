<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Payment;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Payment $payment)
    {}

    public function handle(): void
    {
        // Process payment with external gateway
        $this->payment->update(['status' => 'completed', 'completed_at' => now()]);
        
        // Notify relevant users
        $this->payment->order->update(['payment_status' => 'paid']);
    }
}
