<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketPrice;
use Carbon\Carbon;

class MarketPriceSeeder extends Seeder
{
    public function run(): void
    {
        // Sample realistic Ethiopian commodities and benchmark markets
        $commodities = [
            [
                'crop' => 'Teff (Magna / White)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 11800, 'trend' => 'increasing', 'notes' => 'High urban demand, prime quality from Gojjam'],
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 11200, 'trend' => 'stable', 'notes' => 'Steady supply arriving from Arsi'],
                    ['loc' => 'Bahir Dar Market', 'reg' => 'Amhara', 'price' => 10600, 'trend' => 'increasing', 'notes' => 'Local regional wholesale depot'],
                    ['loc' => 'Hawassa Central', 'reg' => 'Sidama', 'price' => 11400, 'trend' => 'increasing', 'notes' => 'Southern distribution transit hub'],
                ]
            ],
            [
                'crop' => 'Teff (Sergegna / Mixed)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 9600, 'trend' => 'stable', 'notes' => 'Consistent daily turnover in grain terminal'],
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 9100, 'trend' => 'decreasing', 'notes' => 'Post-harvest deliveries increasing stock'],
                    ['loc' => 'Shashemene Depot', 'reg' => 'Oromia', 'price' => 9300, 'trend' => 'stable', 'notes' => 'Solid trade volume for wholesale distributors'],
                ]
            ],
            [
                'crop' => 'Coffee (Arabica / Sidama Grade 1)',
                'unit' => 'kg',
                'markets' => [
                    ['loc' => 'Hawassa Central', 'reg' => 'Sidama', 'price' => 310, 'trend' => 'increasing', 'notes' => 'Strong export bidding at local ECX-certified terminal'],
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 345, 'trend' => 'increasing', 'notes' => 'Premium washed grade specialty lots'],
                    ['loc' => 'Dilla Hub', 'reg' => 'SNNPR', 'price' => 295, 'trend' => 'stable', 'notes' => 'Direct farm-gate aggregation lots'],
                ]
            ],
            [
                'crop' => 'Coffee (Jimma / Limu Grade 2)',
                'unit' => 'kg',
                'markets' => [
                    ['loc' => 'Jimma Market Hub', 'reg' => 'Oromia', 'price' => 260, 'trend' => 'stable', 'notes' => 'Steady volume from western highlands'],
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 285, 'trend' => 'increasing', 'notes' => 'Active commercial roaster demand'],
                ]
            ],
            [
                'crop' => 'Wheat (Milling White)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 5400, 'trend' => 'stable', 'notes' => 'Major flour mills securing monthly grain contracts'],
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 5800, 'trend' => 'increasing', 'notes' => 'High urban bakery and mill demand'],
                    ['loc' => 'Bahir Dar Market', 'reg' => 'Amhara', 'price' => 5100, 'trend' => 'decreasing', 'notes' => 'Ample local irrigated harvest inflow'],
                ]
            ],
            [
                'crop' => 'Maize (White Corn)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Shashemene Depot', 'reg' => 'Oromia', 'price' => 3800, 'trend' => 'decreasing', 'notes' => 'High production zone with competitive bulk rates'],
                    ['loc' => 'Jimma Market Hub', 'reg' => 'Oromia', 'price' => 3650, 'trend' => 'stable', 'notes' => 'Stable feed & human consumption inventory'],
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 4300, 'trend' => 'stable', 'notes' => 'Transport costs factored from Rift Valley'],
                ]
            ],
            [
                'crop' => 'Red Kidney Beans (Haricot)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Hawassa Central', 'reg' => 'Sidama', 'price' => 6800, 'trend' => 'increasing', 'notes' => 'High regional export demand to neighboring countries'],
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 6500, 'trend' => 'stable', 'notes' => 'Canning and packaging buyers active'],
                    ['loc' => 'Dire Dawa Terminal', 'reg' => 'Dire Dawa', 'price' => 7100, 'trend' => 'increasing', 'notes' => 'Cross-border corridor supply gateway'],
                ]
            ],
            [
                'crop' => 'Sesame Seed (Humera White)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Gondar Commercial Hub', 'reg' => 'Amhara', 'price' => 14800, 'trend' => 'increasing', 'notes' => 'Premium export oilseed grade, top international pricing'],
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 15600, 'trend' => 'increasing', 'notes' => 'Export warehouse consolidated lots'],
                ]
            ],
            [
                'crop' => 'Soybeans (Non-GMO)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Bahir Dar Market', 'reg' => 'Amhara', 'price' => 5900, 'trend' => 'stable', 'notes' => 'Industrial edible oil & animal feed factories buying'],
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 6200, 'trend' => 'increasing', 'notes' => 'Processing facility delivery contracts'],
                ]
            ],
            [
                'crop' => 'Red Onions (Shallots)',
                'unit' => 'kg',
                'markets' => [
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 68, 'trend' => 'decreasing', 'notes' => 'Meki and Ziway irrigated shipments arriving'],
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 58, 'trend' => 'decreasing', 'notes' => 'Close proximity to Rift Valley farms'],
                    ['loc' => 'Bahir Dar Market', 'reg' => 'Amhara', 'price' => 62, 'trend' => 'stable', 'notes' => 'Lake Tana basin supply stable'],
                ]
            ],
            [
                'crop' => 'Fresh Tomatoes',
                'unit' => 'kg',
                'markets' => [
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 55, 'trend' => 'increasing', 'notes' => 'Seasonal perishable price fluctuation'],
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 45, 'trend' => 'stable', 'notes' => 'Direct from Wonji and Koka greenhouses'],
                    ['loc' => 'Hawassa Central', 'reg' => 'Sidama', 'price' => 50, 'trend' => 'increasing', 'notes' => 'Hospitality and restaurant demand'],
                ]
            ],
            [
                'crop' => 'Potatoes (Local Table)',
                'unit' => 'kg',
                'markets' => [
                    ['loc' => 'Shashemene Depot', 'reg' => 'Oromia', 'price' => 38, 'trend' => 'stable', 'notes' => 'West Arsi highland production steady'],
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 48, 'trend' => 'increasing', 'notes' => 'Wholesale sacks sold in bulk'],
                    ['loc' => 'Bahir Dar Market', 'reg' => 'Amhara', 'price' => 42, 'trend' => 'stable', 'notes' => 'Consistent local farmer supply'],
                ]
            ],
            [
                'crop' => 'Chickpeas (Desi / Kabuli)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 8100, 'trend' => 'increasing', 'notes' => 'Bishoftu and Becho high-yield legume harvests'],
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 8600, 'trend' => 'increasing', 'notes' => 'Shiro manufacturing processors actively procuring'],
                ]
            ],
            [
                'crop' => 'Barley (Malting Grade)',
                'unit' => 'quintal',
                'markets' => [
                    ['loc' => 'Adama (Central Hub)', 'reg' => 'Oromia', 'price' => 6400, 'trend' => 'stable', 'notes' => 'Commercial breweries contracting under quality specs'],
                    ['loc' => 'Bahir Dar Market', 'reg' => 'Amhara', 'price' => 6100, 'trend' => 'stable', 'notes' => 'Highland malting barley deliveries'],
                ]
            ],
            [
                'crop' => 'Fresh Avocado (Hass / Local)',
                'unit' => 'kg',
                'markets' => [
                    ['loc' => 'Hawassa Central', 'reg' => 'Sidama', 'price' => 70, 'trend' => 'stable', 'notes' => 'Wondo Genet and Yirgalem orchard harvests'],
                    ['loc' => 'Addis Ababa (Merkato)', 'reg' => 'Addis Ababa', 'price' => 90, 'trend' => 'increasing', 'notes' => 'Fresh fruit markets and juice house wholesale'],
                ]
            ],
        ];

        // Seed current and recent historical entries (last 14 days)
        $today = Carbon::today();
        foreach ($commodities as $c) {
            foreach ($c['markets'] as $m) {
                // Today's entry
                MarketPrice::create([
                    'crop_name' => $c['crop'],
                    'market_location' => $m['loc'],
                    'market_region' => $m['reg'],
                    'price_per_unit' => $m['price'],
                    'unit' => $c['unit'],
                    'price_trend' => $m['trend'],
                    'price_date' => $today->toDateString(),
                    'notes' => $m['notes'],
                    'created_at' => $today->copy()->setTime(8, 30),
                    'updated_at' => $today->copy()->setTime(8, 30),
                ]);

                // 7 days ago historical record
                $pastPrice = match ($m['trend']) {
                    'increasing' => round($m['price'] * 0.94, 2),
                    'decreasing' => round($m['price'] * 1.06, 2),
                    default => round($m['price'] * 0.99, 2),
                };

                MarketPrice::create([
                    'crop_name' => $c['crop'],
                    'market_location' => $m['loc'],
                    'market_region' => $m['reg'],
                    'price_per_unit' => $pastPrice,
                    'unit' => $c['unit'],
                    'price_trend' => $m['trend'],
                    'price_date' => $today->copy()->subDays(7)->toDateString(),
                    'notes' => 'Previous week official settlement price',
                    'created_at' => $today->copy()->subDays(7)->setTime(9, 0),
                    'updated_at' => $today->copy()->subDays(7)->setTime(9, 0),
                ]);
            }
        }
    }
}
