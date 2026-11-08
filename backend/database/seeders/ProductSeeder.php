<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Distinct Regional Farmers and Agricultural Cooperatives
        $farmersData = [
            'bahir_dar' => [
                'email' => 'farmer@agritech.com',
                'user_name' => 'Bahir Dar Agro Farmers Cooperative',
                'farm_name' => 'Lake Tana Basin Organic Farms',
                'cooperative_name' => 'Lake Tana Agro Producers Union',
                'region' => 'Amhara',
                'zone' => 'West Gojjam',
                'woreda' => 'Bahir Dar',
                'phone' => '251934318549',
                'reg_number' => 'FRM-ETH-00421',
                'bank_name' => 'Awash Bank',
                'bank_account' => '1234567890',
            ],
            'hawassa' => [
                'email' => 'hawassa.farmer@agritech.com',
                'user_name' => 'Hawassa Green Valley Farmers Cooperative',
                'farm_name' => 'Rift Valley Fresh Vegetable Farm',
                'cooperative_name' => 'Hawassa Green Valley Farmers Cooperative',
                'region' => 'Sidama',
                'zone' => 'Hawassa',
                'woreda' => 'Hawassa Zuria',
                'phone' => '251916820001',
                'reg_number' => 'FRM-ETH-00422',
                'bank_name' => 'Commercial Bank of Ethiopia',
                'bank_account' => '1000182736451',
            ],
            'bishoftu' => [
                'email' => 'bishoftu.farmer@agritech.com',
                'user_name' => 'Bishoftu Fresh Horticulture Union',
                'farm_name' => 'Bishoftu Greenhouses & Vegetable Farm',
                'cooperative_name' => 'Bishoftu Fresh Horticulture Union',
                'region' => 'Oromia',
                'zone' => 'East Shewa',
                'woreda' => 'Bishoftu',
                'phone' => '251911450002',
                'reg_number' => 'FRM-ETH-00423',
                'bank_name' => 'Dashen Bank',
                'bank_account' => '2000918273645',
            ],
            'dessie' => [
                'email' => 'dessie.farmer@agritech.com',
                'user_name' => 'South Wollo Grain & Maize Growers Union',
                'farm_name' => 'Highland Cereal & Maize Fields',
                'cooperative_name' => 'South Wollo Grain & Maize Growers Union',
                'region' => 'Amhara',
                'zone' => 'South Wollo',
                'woreda' => 'Dessie',
                'phone' => '251933120003',
                'reg_number' => 'FRM-ETH-00424',
                'bank_name' => 'Abyssinia Bank',
                'bank_account' => '3000817263541',
            ],
            'adama' => [
                'email' => 'adama.farmer@agritech.com',
                'user_name' => 'Adama Agro Farmers Cooperative Union',
                'farm_name' => 'Central Plains Agro Farm',
                'cooperative_name' => 'Adama Agro Farmers Cooperative Union',
                'region' => 'Oromia',
                'zone' => 'East Shewa',
                'woreda' => 'Adama',
                'phone' => '251922890004',
                'reg_number' => 'FRM-ETH-00425',
                'bank_name' => 'Cooperative Bank of Oromia',
                'bank_account' => '4000716253412',
            ],
            'ambo' => [
                'email' => 'ambo.farmer@agritech.com',
                'user_name' => 'Ambo Cereal Producers Cooperative',
                'farm_name' => 'Ambo Guder Agricultural Farm',
                'cooperative_name' => 'Ambo Cereal Producers Cooperative',
                'region' => 'Oromia',
                'zone' => 'West Shewa',
                'woreda' => 'Ambo',
                'phone' => '251911780005',
                'reg_number' => 'FRM-ETH-00426',
                'bank_name' => 'Cooperative Bank of Oromia',
                'bank_account' => '5000615243321',
            ],
        ];

        $farmerModels = [];
        foreach ($farmersData as $key => $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['user_name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'farmer',
                    'region' => $data['region'],
                    'zone' => $data['zone'],
                    'woreda' => $data['woreda'],
                    'is_active' => true,
                ]
            );

            $farmer = Farmer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'farmer_registration_number' => $data['reg_number'],
                    'farm_name' => $data['farm_name'],
                    'cooperative_name' => $data['cooperative_name'],
                    'region' => $data['region'],
                    'zone' => $data['zone'],
                    'woreda' => $data['woreda'],
                    'farm_type' => 'mixed',
                    'verification_status' => 'verified',
                    'bank_name' => $data['bank_name'],
                    'bank_account' => $data['bank_account'],
                ]
            );

            $farmerModels[$key] = $farmer;
        }

        // 2. Map existing products to their correct respective farmers
        // Product #6 (Tomato, Hawassa) -> Hawassa Green Valley Farmers Cooperative
        $p6 = Product::find(6);
        if ($p6 && isset($farmerModels['hawassa'])) {
            $p6->update([
                'farmer_id' => $farmerModels['hawassa']->id,
                'origin_location' => 'Hawassa',
            ]);
        }

        // Product #7 (Tomato, Bishoftu) -> Bishoftu Fresh Horticulture Union
        $p7 = Product::find(7);
        if ($p7 && isset($farmerModels['bishoftu'])) {
            $p7->update([
                'farmer_id' => $farmerModels['bishoftu']->id,
                'origin_location' => 'Bishoftu',
            ]);
        }

        // Product #9 (Maize, Dessie) -> South Wollo Grain & Maize Growers Union
        $p9 = Product::find(9);
        if ($p9 && isset($farmerModels['dessie'])) {
            $p9->update([
                'farmer_id' => $farmerModels['dessie']->id,
                'origin_location' => 'Dessie',
            ]);
        }

        // Product #10 (Maize, Adama) -> Adama Agro Farmers Cooperative Union
        $p10 = Product::find(10);
        if ($p10 && isset($farmerModels['adama'])) {
            $p10->update([
                'farmer_id' => $farmerModels['adama']->id,
                'origin_location' => 'Adama',
            ]);
        }
    }
}
