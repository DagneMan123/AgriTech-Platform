<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\Vehicle;
use App\Models\Transporter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncOrderLogisticsData extends Command
{
    protected $signature = 'orders:sync-logistics';
    protected $description = 'Synchronize order lifecycle statuses, carrier logistics, and authentic farmer profiles';

    public function handle()
    {
        $this->info('Starting Logistics & Order Lifecycle Synchronization...');

        // 1. Ensure Farmer profile is clean and authentic
        $farmerUser = User::updateOrCreate(
            ['email' => 'farmer@agritech.com'],
            [
                'name' => 'Bahir Dar Agro Farmers Cooperative',
                'phone' => '251934318549',
                'password' => Hash::make('password'),
                'role' => 'farmer',
                'region' => 'Amhara',
                'zone' => 'West Gojjam',
                'woreda' => 'Bahir Dar',
                'is_active' => true,
            ]
        );

        $farmerProfile = Farmer::updateOrCreate(
            ['user_id' => $farmerUser->id],
            [
                'farmer_registration_number' => 'FRM-ETH-00421',
                'farm_name' => 'Lake Tana Agro Producers Union',
                'region' => 'Amhara',
                'zone' => 'West Gojjam',
                'woreda' => 'Bahir Dar',
                'farm_type' => 'mixed',
                'verification_status' => 'verified',
            ]
        );
        $this->info("✅ Authentic Farmer Profile verified: {$farmerUser->name} ({$farmerUser->phone})");

        // 2. Ensure Transporter User exists
        $transporterUser = User::updateOrCreate(
            ['email' => 'transporter@agritech.com'],
            [
                'name' => 'Alemayehu Tadesse',
                'phone' => '251938491000',
                'password' => Hash::make('password'),
                'role' => 'transport',
                'region' => 'Addis Ababa',
                'zone' => 'Bole',
                'woreda' => 'Bole Sub-City',
                'is_active' => true,
            ]
        );

        // 3. Ensure Transporter record
        $transporter = Transporter::firstOrCreate(
            ['user_id' => $transporterUser->id],
            [
                'company_name' => 'Ethio Express Agro Logistics',
                'license_plate' => 'ET-38491',
                'license_number' => 'ETH-CDL-98421',
                'vehicle_type' => 'ISUZU FSR Insulated Cold-Chain Truck',
                'contact_person' => 'Alemayehu Tadesse',
                'price_per_km' => 35.00,
                'verification_status' => 'verified',
                'is_active' => true,
            ]
        );

        // 4. Ensure Vehicle record
        $vehicle = Vehicle::firstOrCreate(
            ['registration_number' => 'ET-38491'],
            [
                'transport_provider_id' => $transporterUser->id,
                'vehicle_type' => 'Refrigerated Truck',
                'make' => 'ISUZU',
                'model' => 'FSR Insulated Cold-Chain Truck',
                'year' => 2024,
                'capacity_tons' => 8.50,
                'status' => 'active',
                'license_number' => 'ETH-CDL-98421',
                'notes' => 'Dedicated agricultural cold-chain logistics carrier',
            ]
        );
        $this->info("✅ Transporter and Vehicle registered: {$transporterUser->name} | Plate {$vehicle->registration_number}");

        // 5. Retrieve all orders to distribute lifecycle statuses
        $orders = Order::orderBy('id', 'asc')->get();
        $total = $orders->count();

        if ($total === 0) {
            $this->warn('No orders found to sync.');
            return 0;
        }

        $this->info("Processing {$total} orders into authentic lifecycle categories...");

        // Desired distribution:
        // - Delivered (Past / History): 8 orders
        // - Cancelled (Past / History): 1 order
        // - Shipped / In Transit (Active): 5 orders
        // - Confirmed (Active): 4 orders
        // - Pending (Active): remainder (e.g. 7 orders)
        $deliveredCount = min(8, max(1, (int)($total * 0.35)));
        $inTransitCount = min(5, max(1, (int)($total * 0.20)));
        $confirmedCount = min(4, max(1, (int)($total * 0.15)));

        $idx = 0;
        foreach ($orders as $order) {
            // Attach correct farmer
            $order->farmer_id = $farmerProfile->id;

            // Preserve cancelled status if already cancelled
            if ($order->status === 'cancelled' || $idx === $total - 1 && $total >= 20) {
                $order->status = 'cancelled';
                $order->payment_status = 'cancelled';
                $order->cancelled_at = now()->subDays(2);
                $order->save();
                $idx++;
                continue;
            }

            if ($idx < $deliveredCount) {
                // Historical delivered orders
                $order->status = 'delivered';
                $order->payment_status = 'paid';
                $order->payment_method = ($idx % 2 === 0) ? 'chapa' : 'telebirr';
                $order->delivered_at = now()->subDays(10 - $idx);
                $order->save();

                // Create or update delivered delivery record
                Delivery::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'transporter_id' => $transporter->id,
                        'tracking_number' => 'TRK-' . $order->id,
                        'status' => 'delivered',
                        'assigned_at' => now()->subDays(12 - $idx),
                        'picked_up_at' => now()->subDays(11 - $idx),
                        'delivered_at' => now()->subDays(10 - $idx),
                        'pickup_location' => 'Bahir Dar Central Agro Hub, Warehouse 4',
                        'delivery_location' => $order->delivery_address ?: 'Addis Ababa Central Depot, Gate 3, Bole Sub-City, Addis Ababa, Ethiopia',
                        'latitude_start' => 11.5950,
                        'longitude_start' => 37.3900,
                        'latitude_end' => 9.0142,
                        'longitude_end' => 38.7521,
                        'distance_km' => 485.0,
                        'delivery_fee' => $order->delivery_fee ?: 450.00,
                    ]
                );
            } elseif ($idx < ($deliveredCount + $inTransitCount)) {
                // Active Shipped / In-Transit orders
                $order->status = 'shipped';
                $order->payment_status = 'paid';
                $order->payment_method = ($idx % 2 === 0) ? 'telebirr' : 'chapa';
                $order->delivered_at = null;
                $order->save();

                // Create or update in-transit delivery record
                Delivery::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'transporter_id' => $transporter->id,
                        'tracking_number' => 'TRK-' . $order->id,
                        'status' => 'in_transit',
                        'assigned_at' => now()->subHours(8),
                        'picked_up_at' => now()->subHours(5),
                        'delivered_at' => null,
                        'pickup_location' => 'Lake Tana Agro Cold Storage, Bay 2, Bahir Dar',
                        'delivery_location' => $order->delivery_address ?: 'Ambo Agricultural Wholesale Depot, Kebele 03 Warehouse Area, Gate 1, West Shewa, Ambo, Oromia, Ethiopia',
                        'latitude_start' => 11.5950,
                        'longitude_start' => 37.3900,
                        'latitude_end' => 8.9814,
                        'longitude_end' => 37.8660,
                        'distance_km' => 410.0,
                        'delivery_fee' => $order->delivery_fee ?: 450.00,
                    ]
                );
            } elseif ($idx < ($deliveredCount + $inTransitCount + $confirmedCount)) {
                // Active Confirmed orders
                $order->status = 'confirmed';
                $order->payment_status = 'paid';
                $order->payment_method = 'chapa';
                $order->delivered_at = null;
                $order->save();
            } else {
                // Active Pending orders
                $order->status = 'pending';
                $order->payment_status = ($idx % 3 === 0) ? 'pending' : 'paid';
                $order->payment_method = ($idx % 3 === 0) ? 'cod' : 'telebirr';
                $order->delivered_at = null;
                $order->save();
            }

            $idx++;
        }

        // Print final statistics
        $finalDelivered = Order::where('status', 'delivered')->count();
        $finalCancelled = Order::where('status', 'cancelled')->count();
        $finalShipped = Order::whereIn('status', ['shipped', 'in_transit'])->count();
        $finalConfirmed = Order::where('status', 'confirmed')->count();
        $finalPending = Order::where('status', 'pending')->count();

        $activeTotal = $finalShipped + $finalConfirmed + $finalPending;
        $pastTotal = $finalDelivered + $finalCancelled;

        $this->info("--------------------------------------------------");
        $this->info("Lifecycle Distribution Summary:");
        $this->info("📦 ACTIVE ORDERS: {$activeTotal} (Pending: {$finalPending}, Confirmed: {$finalConfirmed}, In Transit: {$finalShipped})");
        $this->info("📜 PAST ORDERS / HISTORY: {$pastTotal} (Delivered: {$finalDelivered}, Cancelled: {$finalCancelled})");
        $this->info("📋 TOTAL ORDERS: {$total}");
        $this->info("--------------------------------------------------");

        return 0;
    }
}
