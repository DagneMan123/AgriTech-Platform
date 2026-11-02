<?php

namespace App\Services;

class ShippingCalculatorService
{
    public const WEIGHT_FLAT_THRESHOLD_KG = 5.0;
    public const DISTANCE_SHORT_THRESHOLD_KM = 30.0;

    /**
     * Major Ethiopian Agricultural Hubs & Production Basins
     */
    public const ETHIOPIAN_ORIGIN_HUBS = [
        'Adama' => ['region' => 'Oromia', 'dist_to_addis' => 95, 'label' => 'Adama (Oromia Agro-Logistics Center)'],
        'Hawassa' => ['region' => 'Sidama', 'dist_to_addis' => 275, 'label' => 'Hawassa (Sidama Rift Valley Basin)'],
        'Bahir Dar' => ['region' => 'Amhara', 'dist_to_addis' => 495, 'label' => 'Bahir Dar (Lake Tana Basin)'],
        'Addis Ababa' => ['region' => 'Addis Ababa', 'dist_to_addis' => 25, 'label' => 'Addis Ababa (Central Terminal)'],
        'Jimma' => ['region' => 'Oromia', 'dist_to_addis' => 350, 'label' => 'Jimma (Coffee & Agro Basin)'],
        'Gondar' => ['region' => 'Amhara', 'dist_to_addis' => 660, 'label' => 'Gondar (North Amhara Basin)'],
        'Dire Dawa' => ['region' => 'Dire Dawa', 'dist_to_addis' => 450, 'label' => 'Dire Dawa (Eastern Corridor)'],
        'Mekelle' => ['region' => 'Tigray', 'dist_to_addis' => 780, 'label' => 'Mekelle (Tigray Basin)'],
        'Shashamane' => ['region' => 'Oromia', 'dist_to_addis' => 245, 'label' => 'Shashamane (Rift Valley Junction)'],
        'Nekemte' => ['region' => 'Oromia', 'dist_to_addis' => 330, 'label' => 'Nekemte (Western Grain Belt)'],
        'Bishoftu' => ['region' => 'Oromia', 'dist_to_addis' => 45, 'label' => 'Bishoftu (Central Agro Valley)'],
        'Arba Minch' => ['region' => 'SNNPR', 'dist_to_addis' => 440, 'label' => 'Arba Minch (Southern Fruit Basin)'],
        'Dessie' => ['region' => 'Amhara', 'dist_to_addis' => 385, 'label' => 'Dessie (Eastern Amhara)'],
        'Debre Markos' => ['region' => 'Amhara', 'dist_to_addis' => 300, 'label' => 'Debre Markos (Gojjam Basin)'],
    ];

    /**
     * Known highway inter-city / direct route distance matrix (km)
     */
    public const DIRECT_DISTANCES = [
        'Adama:Addis Ababa' => 95,
        'Addis Ababa:Adama' => 95,
        'Adama:Hawassa' => 185,
        'Hawassa:Adama' => 185,
        'Adama:Dire Dawa' => 355,
        'Dire Dawa:Adama' => 355,
        'Hawassa:Addis Ababa' => 275,
        'Addis Ababa:Hawassa' => 275,
        'Hawassa:Shashamane' => 25,
        'Shashamane:Hawassa' => 25,
        'Bahir Dar:Addis Ababa' => 495,
        'Addis Ababa:Bahir Dar' => 495,
        'Bahir Dar:Gondar' => 175,
        'Gondar:Bahir Dar' => 175,
        'Bishoftu:Addis Ababa' => 45,
        'Addis Ababa:Bishoftu' => 45,
        'Bishoftu:Adama' => 50,
        'Adama:Bishoftu' => 50,
        'Jimma:Addis Ababa' => 350,
        'Addis Ababa:Jimma' => 350,
        'Dire Dawa:Harari' => 55,
        'Harari:Dire Dawa' => 55,
        'Dire Dawa:Addis Ababa' => 450,
        'Addis Ababa:Dire Dawa' => 450,
        'Mekelle:Addis Ababa' => 780,
        'Addis Ababa:Mekelle' => 780,
        'Arba Minch:Hawassa' => 165,
        'Hawassa:Arba Minch' => 165,
    ];

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
            'flat_rate' => 600.0,
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
     * Dynamically calculate distance (km) between product origin and buyer destination
     */
    public static function calculateDistanceKm(?string $origin, ?string $destination): float
    {
        $orig = trim($origin ?: 'Addis Ababa');
        $dest = trim($destination ?: 'Addis Ababa');

        // Parse combo destination strings like "Adama, Oromia" or "Adama (Oromia)"
        if (preg_match('/^([^,(]+)(?:[,\(]\s*([^\)]+)\)?)?$/', $dest, $matches)) {
            $candidateCity = trim($matches[1]);
            $candidateRegion = isset($matches[2]) ? trim($matches[2]) : '';
            if (isset(self::ETHIOPIAN_ORIGIN_HUBS[$candidateCity])) {
                $dest = $candidateCity;
            } elseif (isset(self::ETHIOPIAN_REGIONS_LOGISTICS[$candidateRegion])) {
                $dest = $candidateRegion;
            }
        }

        // Same location = local intra-city transit
        if (strcasecmp($orig, $dest) === 0) {
            return 25.0;
        }

        // Direct pair lookup
        $key1 = "{$orig}:{$dest}";
        $key2 = "{$dest}:{$orig}";
        if (isset(self::DIRECT_DISTANCES[$key1])) {
            return (float) self::DIRECT_DISTANCES[$key1];
        }
        if (isset(self::DIRECT_DISTANCES[$key2])) {
            return (float) self::DIRECT_DISTANCES[$key2];
        }

        // Resolve origin hub info
        $origInfo = self::ETHIOPIAN_ORIGIN_HUBS[$orig] ?? null;
        $origDistToAddis = $origInfo ? (float) $origInfo['dist_to_addis'] : 25.0;
        $origRegion = $origInfo ? $origInfo['region'] : $orig;

        // If destination is one of the administrative regions
        if (isset(self::ETHIOPIAN_REGIONS_LOGISTICS[$dest])) {
            $destDistToAddis = (float) self::ETHIOPIAN_REGIONS_LOGISTICS[$dest]['distance_km'];

            // Same region
            if (strcasecmp($origRegion, $dest) === 0) {
                return min(60.0, $destDistToAddis * 0.4);
            }

            // Transit through or towards Addis corridor
            if (strcasecmp($dest, 'Addis Ababa') === 0) {
                return max(25.0, $origDistToAddis);
            }
            if (strcasecmp($orig, 'Addis Ababa') === 0) {
                return max(25.0, $destDistToAddis);
            }

            // Route through hub network
            return round($origDistToAddis + ($destDistToAddis * 0.85));
        }

        // Destination is another hub
        $destInfo = self::ETHIOPIAN_ORIGIN_HUBS[$dest] ?? null;
        if ($destInfo) {
            $destDistToAddis = (float) $destInfo['dist_to_addis'];
            $destRegion = $destInfo['region'];

            if (strcasecmp($origRegion, $destRegion) === 0) {
                return 40.0;
            }
            return round($origDistToAddis + $destDistToAddis);
        }

        // Fallback default
        return 95.0;
    }

    /**
     * Get primary origin location or max-distance origin for an array/collection of cart items
     */
    public static function resolveCartOriginAndDistance($cartItems, string $destination = 'Addis Ababa'): array
    {
        $maxDistance = 25.0;
        $primaryOrigin = 'Addis Ababa';
        $originsFound = [];

        foreach ($cartItems as $item) {
            $product = is_array($item) ? ($item['product'] ?? null) : ($item->product ?? null);
            $orig = null;
            if (is_array($item) && !empty($item['origin_location'])) {
                $orig = $item['origin_location'];
            } elseif ($product) {
                $orig = is_array($product) ? ($product['origin_location'] ?? null) : ($product->origin_location ?? null);
            }

            $orig = $orig ?: 'Addis Ababa';
            $originsFound[] = $orig;
            $dist = self::calculateDistanceKm($orig, $destination);

            if ($dist >= $maxDistance) {
                $maxDistance = $dist;
                $primaryOrigin = $orig;
            }
        }

        return [
            'origin' => $primaryOrigin,
            'all_origins' => array_unique($originsFound),
            'distance_km' => $maxDistance,
            'destination' => $destination
        ];
    }

    /**
     * Convert an item's unit & quantity to normalized kilograms
     */
    public static function getItemWeightKg($product, float|int $quantity): float
    {
        $qty = max(0, (float) $quantity);
        $rawUnit = strtolower(trim($product->unit ?? $product->quantity_unit ?? 'kg'));
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
     * 1. Light Loads (<= 5kg): Fixed Flat Rate (Standard: ETB 350, Express: ETB 600, Cold-Chain: ETB 1200)
     * 2. Bulk/Heavy Loads (> 5kg): Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)
     */
    public static function calculateFee(
        float $weightKg,
        string $region = 'Addis Ababa',
        string $tierKey = 'standard',
        ?string $origin = 'Addis Ababa'
    ): float {
        $breakdown = self::getBreakdown($weightKg, $region, $tierKey, $origin);
        return $breakdown['total_fee'];
    }

    /**
     * Get detailed breakdown of the dynamic shipping calculation
     */
    public static function getBreakdown(
        float $weightKg,
        string $destination = 'Addis Ababa',
        string $tierKey = 'standard',
        ?string $origin = 'Addis Ababa'
    ): array {
        $tier = self::SHIPPING_TIERS[$tierKey] ?? self::SHIPPING_TIERS['standard'];
        $distanceKm = self::calculateDistanceKm($origin ?: 'Addis Ababa', $destination);

        // -------------------------------------------------------------------------
        // THRESHOLD CHECK: Light Loads (<= 5 kg) vs Bulk / Heavy Shipments (> 5 kg)
        // -------------------------------------------------------------------------
        $isLightLoad = ($weightKg <= self::WEIGHT_FLAT_THRESHOLD_KG);

        if ($isLightLoad) {
            // 1. Small Shipments (<= 5 kg):
            // Do NOT multiply by distance matrix (ignore distance km rate).
            // Charge a fixed, affordable flat rate based on shipping tier.
            return [
                'tier' => $tier['key'],
                'title' => $tier['title'],
                'origin' => $origin ?: 'Addis Ababa',
                'destination' => $destination,
                'weight_kg' => round($weightKg, 2),
                'distance_km' => $distanceKm,
                'is_flat_rate' => true,
                'base_fee' => (float) $tier['flat_rate'],
                'rate_per_kg' => (float) $tier['rate_per_kg'],
                'weight_cost' => 0.0,
                'rate_per_km' => (float) $tier['rate_per_km'],
                'distance_cost' => 0.0,
                'total_fee' => (float) $tier['flat_rate'],
                'formula_str' => '',
            ];
        }

        // Dynamic Weight & Distance Matrix: Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)
        $weightCost = round($weightKg * $tier['rate_per_kg'], 2);
        $distanceCost = round($distanceKm * $tier['rate_per_km'], 2);
        $rawTotal = $tier['base_fee'] + $weightCost + $distanceCost;
        
        // Never fall below minimum flat rate, rounded to nearest 10 ETB
        $totalFee = max($tier['flat_rate'], round($rawTotal / 10) * 10);

        return [
            'tier' => $tier['key'],
            'title' => $tier['title'],
            'origin' => $origin ?: 'Addis Ababa',
            'destination' => $destination,
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

    /**
     * Group cart items by their distinct agricultural origin locations
     */
    public static function groupCartItemsByOrigin($cartItems, string $destination = 'Addis Ababa'): array
    {
        $groups = [];

        foreach ($cartItems as $item) {
            $product = is_array($item) ? ($item['product'] ?? null) : ($item->product ?? null);
            $orig = null;

            if (is_array($item) && !empty($item['origin_location'])) {
                $orig = $item['origin_location'];
            } elseif ($product) {
                $orig = is_array($product) ? ($product['origin_location'] ?? null) : ($product->origin_location ?? null);
            }

            $orig = trim($orig ?: 'Addis Ababa');
            $qty = is_array($item) ? ($item['quantity'] ?? 1) : ($item->quantity ?? 1);
            $itemWeight = $product ? self::getItemWeightKg($product, $qty) : max(1.0, (float) $qty);

            if (!isset($groups[$orig])) {
                $groups[$orig] = [
                    'origin' => $orig,
                    'destination' => $destination,
                    'distance_km' => self::calculateDistanceKm($orig, $destination),
                    'weight_kg' => 0.0,
                    'has_perishables' => false,
                    'items' => [],
                ];
            }

            $groups[$orig]['weight_kg'] += $itemWeight;
            $groups[$orig]['items'][] = $item;

            if ($product) {
                $cat = strtolower(trim(is_array($product) ? ($product['category'] ?? '') : ($product->category ?? '')));
                $name = strtolower(trim(is_array($product) ? ($product['name'] ?? '') : ($product->name ?? '')));
                foreach (self::PERISHABLE_CATEGORIES as $p) {
                    if (str_contains($cat, $p) || str_contains($name, $p)) {
                        $groups[$orig]['has_perishables'] = true;
                        break;
                    }
                }
            }
        }

        foreach ($groups as &$g) {
            $g['weight_kg'] = max(1.0, round($g['weight_kg'], 2));
        }

        return array_values($groups);
    }

    /**
     * Multi-Origin Split Shipping Breakdown for a specific shipping tier
     */
    public static function getMultiOriginBreakdown(
        $cartItems,
        string $destination = 'Addis Ababa',
        string $tierKey = 'standard'
    ): array {
        $groups = self::groupCartItemsByOrigin($cartItems, $destination);

        if (empty($groups)) {
            $groups = [[
                'origin' => 'Addis Ababa',
                'destination' => $destination,
                'distance_km' => self::calculateDistanceKm('Addis Ababa', $destination),
                'weight_kg' => 1.0,
                'has_perishables' => false,
                'items' => [],
            ]];
        }

        $routes = [];
        $totalFee = 0.0;
        $totalWeight = 0.0;

        foreach ($groups as $group) {
            $routeBreakdown = self::getBreakdown($group['weight_kg'], $destination, $tierKey, $group['origin']);
            $routeFee = $routeBreakdown['total_fee'];
            $totalFee += $routeFee;
            $totalWeight += $group['weight_kg'];

            $routes[] = [
                'origin' => $group['origin'],
                'destination' => $destination,
                'distance_km' => $group['distance_km'],
                'weight_kg' => $group['weight_kg'],
                'item_count' => count($group['items']),
                'has_perishables' => $group['has_perishables'],
                'total_fee' => $routeFee,
                'is_flat_rate' => $routeBreakdown['is_flat_rate'],
                'formula_str' => $routeBreakdown['formula_str'],
                'breakdown' => $routeBreakdown,
            ];
        }

        return [
            'tier' => $tierKey,
            'destination' => $destination,
            'total_weight_kg' => round($totalWeight, 2),
            'total_fee' => (float) $totalFee,
            'origin_count' => count($routes),
            'is_multi_origin' => count($routes) > 1,
            'routes' => $routes,
        ];
    }

    /**
     * Calculate multi-origin fee sum for the cart
     */
    public static function calculateMultiOriginFee(
        $cartItems,
        string $destination = 'Addis Ababa',
        string $tierKey = 'standard'
    ): float {
        $breakdown = self::getMultiOriginBreakdown($cartItems, $destination, $tierKey);
        return $breakdown['total_fee'];
    }

    /**
     * Get dynamic shipping options with multi-origin split calculations
     */
    public static function getDynamicShippingOptions($cartItems, string $destination = 'Addis Ababa'): array
    {
        $hasPerishables = self::hasPerishables($cartItems);
        $options = [];

        foreach (self::SHIPPING_TIERS as $key => $tier) {
            if ($tier['perishable_only'] && !$hasPerishables) {
                continue;
            }

            $multi = self::getMultiOriginBreakdown($cartItems, $destination, $key);
            $options[] = [
                'key' => $key,
                'title' => $tier['title'],
                'desc' => $multi['is_multi_origin']
                    ? "Split shipment from {$multi['origin_count']} origins → {$destination}"
                    : "Ambient cargo dispatch to {$destination}",
                'fee' => $multi['total_fee'],
                'badge' => $multi['is_multi_origin'] ? "SPLIT DISPATCH ({$multi['origin_count']} ORIGINS)" : null,
                'is_perishable_only' => $tier['perishable_only'],
                'routes' => $multi['routes'],
                'is_multi_origin' => $multi['is_multi_origin'],
                'origin_count' => $multi['origin_count'],
                'breakdown' => $multi['routes'][0]['breakdown'] ?? null,
            ];
        }

        return $options;
    }

}
