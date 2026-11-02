<?php

namespace App\Services;

class ShippingCalculatorService
{
    public const WEIGHT_FLAT_THRESHOLD_KG = 5.0;
    public const DISTANCE_SHORT_THRESHOLD_KM = 30.0;

    public const ETHIOPIAN_REGIONS_LOGISTICS = [
        'Addis Ababa' => ['distance_km' => 25, 'transit_days' => 'Same-day or next-day delivery'],
        'Oromia' => ['distance_km' => 120, 'transit_days' => '1 - 2 business days'],
        'Amhara' => ['distance_km' => 450, 'transit_days' => '2 - 3 business days'],
        'Sidama' => ['distance_km' => 275, 'transit_days' => '1 - 2 business days'],
        'SNNPR' => ['distance_km' => 380, 'transit_days' => '2 - 3 business days'],
        'Dire Dawa' => ['distance_km' => 480, 'transit_days' => '2 - 3 business days'],
        'Harari' => ['distance_km' => 510, 'transit_days' => '2 - 4 business days'],
        'Afar' => ['distance_km' => 580, 'transit_days' => '3 - 4 business days'],
        'Benishangul-Gumuz' => ['distance_km' => 620, 'transit_days' => '3 - 5 business days'],
        'Tigray' => ['distance_km' => 780, 'transit_days' => '3 - 5 business days'],
        'Somali' => ['distance_km' => 630, 'transit_days' => '3 - 5 business days'],
        'Gambela' => ['distance_km' => 770, 'transit_days' => '4 - 5 business days'],
    ];

    public const SHIPPING_TIERS = [
        'standard' => [
            'key' => 'standard',
            'title' => 'Standard Agricultural Freight',
            'desc' => 'Ambient cargo dispatch',
            'flat_rate' => 350.0,
            'base_fee' => 200.0,
            'rate_per_kg' => 10.0,
            'rate_per_km' => 6.0,
            'perishable_only' => false,
        ],
        'express' => [
            'key' => 'express',
            'title' => 'Express Direct Dispatch',
            'desc' => 'Priority dedicated farm-to-door transit',
            'flat_rate' => 650.0,
            'base_fee' => 350.0,
            'rate_per_kg' => 16.0,
            'rate_per_km' => 10.0,
            'perishable_only' => false,
        ],
        'overnight' => [
            'key' => 'overnight',
            'title' => 'Cold-Chain Agro Transport',
            'desc' => 'Climate-controlled (+4°C) for fresh perishable produce',
            'flat_rate' => 1200.0,
            'base_fee' => 700.0,
            'rate_per_kg' => 22.0,
            'rate_per_km' => 15.0,
            'perishable_only' => true,
        ],
    ];

    public const PERISHABLE_CATEGORIES = [
        'vegetables', 'vegetable', 'fruits', 'fruit', 'dairy', 'milk',
        'meat', 'poultry', 'fish', 'fresh produce', 'horticulture', 'flowers'
    ];

    /**
     * Convert an item's unit & quantity to normalized kilograms
     */
    public static function getItemWeightKg($product, float|int $quantity): float
    {
        $qty = max(0, (float) $quantity);
        $rawUnit = strtolower(trim($product->unit ?? $product->quantity_unit ?? 'kg'));
        // Clean pricing unit markers like 'etb/kg'
        $unit = preg_replace('/^(etb|birr|\$)?\s*\/?\s*/i', '', $rawUnit);

        return match ($unit) {
            'kg', 'kilogram', 'kilograms' => $qty,
            'g', 'gram', 'grams' => $qty * 0.001,
            'quintal', 'quintals', 'qtl' => $qty * 100.0,
            'ton', 'tonne', 'tons' => $qty * 1000.0,
            'crate', 'crates' => $qty * 25.0,
            'bag', 'bags', 'sack', 'sacks' => $qty * 50.0,
            'bundle', 'bundles' => $qty * 5.0,
            'piece', 'pieces' => $qty * 2.0,
            'liter', 'liters', 'l' => $qty * 1.0,
            default => $qty * 1.0,
        };
    }

    /**
     * Calculate total weight in kg from an iterable of cart items
     */
    public static function calculateTotalWeightKg($cartItems): float
    {
        $total = 0.0;
        foreach ($cartItems as $item) {
            $product = is_array($item) ? ($item['product'] ?? null) : ($item->product ?? null);
            $qty = is_array($item) ? ($item['quantity'] ?? 1) : ($item->quantity ?? 1);
            if ($product) {
                $total += self::getItemWeightKg($product, $qty);
            } else {
                $total += max(1.0, (float) $qty);
            }
        }
        return max(1.0, $total);
    }

    /**
     * Check if cart items contain perishable produce
     */
    public static function hasPerishables($cartItems): bool
    {
        foreach ($cartItems as $item) {
            $product = is_array($item) ? ($item['product'] ?? null) : ($item->product ?? null);
            if (!$product) continue;
            
            $cat = strtolower(trim(is_array($product) ? ($product['category'] ?? '') : ($product->category ?? '')));
            $name = strtolower(trim(is_array($product) ? ($product['name'] ?? '') : ($product->name ?? '')));

            foreach (self::PERISHABLE_CATEGORIES as $p) {
                if (str_contains($cat, $p) || str_contains($name, $p)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Calculate dynamic shipping fee using Weight & Distance Matrix
     * Formula: Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)
     * For small/medium orders (<= 5kg and distance <= 30km): Flat Rate (ETB 350)
     */
    public static function calculateFee(
        float $weightKg,
        string $region = 'Addis Ababa',
        string $tierKey = 'standard'
    ): float {
        $breakdown = self::getBreakdown($weightKg, $region, $tierKey);
        return $breakdown['total_fee'];
    }

    /**
     * Get detailed breakdown of the shipping calculation
     */
    public static function getBreakdown(
        float $weightKg,
        string $region = 'Addis Ababa',
        string $tierKey = 'standard'
    ): array {
        $tier = self::SHIPPING_TIERS[$tierKey] ?? self::SHIPPING_TIERS['standard'];
        $regionData = self::ETHIOPIAN_REGIONS_LOGISTICS[$region] ?? self::ETHIOPIAN_REGIONS_LOGISTICS['Addis Ababa'];
        $distanceKm = (float) $regionData['distance_km'];

        $isSmallLocal = ($weightKg <= self::WEIGHT_FLAT_THRESHOLD_KG) && ($distanceKm <= self::DISTANCE_SHORT_THRESHOLD_KM);

        if ($isSmallLocal) {
            return [
                'tier' => $tier['key'],
                'title' => $tier['title'],
                'region' => $region,
                'weight_kg' => round($weightKg, 2),
                'distance_km' => $distanceKm,
                'is_flat_rate' => true,
                'base_fee' => $tier['flat_rate'],
                'rate_per_kg' => $tier['rate_per_kg'],
                'weight_cost' => 0.0,
                'rate_per_km' => $tier['rate_per_km'],
                'distance_cost' => 0.0,
                'total_fee' => (float) $tier['flat_rate'],
                'formula_str' => "Flat Rate: ETB {$tier['flat_rate']} (Orders ≤ 5kg & local)",
            ];
        }

        // Dynamic matrix calculation: Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)
        $weightCost = round($weightKg * $tier['rate_per_kg'], 2);
        $distanceCost = round($distanceKm * $tier['rate_per_km'], 2);
        $rawTotal = $tier['base_fee'] + $weightCost + $distanceCost;
        
        // Never fall below the tier's minimum flat rate, rounded to nearest 10 ETB
        $totalFee = max($tier['flat_rate'], round($rawTotal / 10) * 10);

        return [
            'tier' => $tier['key'],
            'title' => $tier['title'],
            'region' => $region,
            'weight_kg' => round($weightKg, 2),
            'distance_km' => $distanceKm,
            'is_flat_rate' => false,
            'base_fee' => (float) $tier['base_fee'],
            'rate_per_kg' => (float) $tier['rate_per_kg'],
            'weight_cost' => $weightCost,
            'rate_per_km' => (float) $tier['rate_per_km'],
            'distance_cost' => $distanceCost,
            'total_fee' => (float) $totalFee,
            'formula_str' => "Base ETB {$tier['base_fee']} + ({$weightKg}kg × {$tier['rate_per_kg']}) + ({$distanceKm}km × {$tier['rate_per_km']}) = ETB {$totalFee}",
        ];
    }
}
