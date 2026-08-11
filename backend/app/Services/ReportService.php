<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSalesReport($startDate, $endDate, $farmerId = null)
    {
        $query = Order::whereBetween('created_at', [$startDate, $endDate]);

        if ($farmerId) {
            $query->whereHas('items', function ($q) use ($farmerId) {
                $q->where('farmer_id', $farmerId);
            });
        }

        return $query->get();
    }

    public function getRevenueReport($startDate, $endDate)
    {
        return Payment::where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(completed_at)'))
            ->selectRaw('DATE(completed_at) as date, SUM(amount) as total')
            ->get();
    }

    public function getDeliveryReport($startDate, $endDate)
    {
        return Delivery::whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->get();
    }
}
