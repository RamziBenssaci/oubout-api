<?php

namespace Database\Seeders;

use App\Models\ShippingAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingAddressSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('shipping_addresses')->truncate();

        $addresses = [
            [
                'id' => 1,
                'title' => 'Belgium',
                'address' => '456 Brussels Avenue',
                'city' => 'Brussels',
                'state' => 'Brussels',
                'zip_code' => 'UM1ZZ649',
            ],
            [
                'id' => 2,
                'title' => 'China Air',
                'address' => '123 Beijing Street',
                'city' => 'Beijing',
                'state' => 'Beijing',
                'zip_code' => 'CN1ZZ649',
            ],
            [
                'id' => 3,
                'title' => 'China-Sea-KG',
                'address' => '456 Shanghai Port',
                'city' => 'Shanghai',
                'state' => 'Shanghai',
                'zip_code' => 'CN2ZZ649',
            ],
            [
                'id' => 4,
                'title' => 'Germany',
                'address' => '22 Berlin Strasse',
                'city' => 'Berlin',
                'state' => 'Berlin',
                'zip_code' => 'DE1ZZ649',
            ],
            [
                'id' => 5,
                'title' => 'India',
                'address' => '9 Mumbai Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'zip_code' => 'IN1ZZ649',
            ],
            [
                'id' => 6,
                'title' => 'Saudi Arabia',
                'address' => '7 Riyadh Avenue',
                'city' => 'Riyadh',
                'state' => 'Riyadh',
                'zip_code' => 'SA1ZZ649',
            ],
            [
                'id' => 7,
                'title' => 'SHEIN - United Arab Emirates - Sea',
                'address' => 'Jebel Ali Free Zone',
                'city' => 'Dubai',
                'state' => 'Dubai',
                'zip_code' => 'AE2ZZ649',
            ],
            [
                'id' => 8,
                'title' => 'Turkey',
                'address' => '789 Istanbul Road',
                'city' => 'Istanbul',
                'state' => 'Istanbul',
                'zip_code' => 'TR1ZZ649',
            ],
            [
                'id' => 9,
                'title' => 'United Arab Emirates - Air',
                'address' => 'Dubai International Airport',
                'city' => 'Dubai',
                'state' => 'Dubai',
                'zip_code' => 'AE1ZZ649',
            ],
            [
                'id' => 10,
                'title' => 'United Arab Emirates - Sea',
                'address' => 'Port Rashid',
                'city' => 'Dubai',
                'state' => 'Dubai',
                'zip_code' => 'AE3ZZ649',
            ],
        ];

        foreach ($addresses as $address) {
            ShippingAddress::create($address);
        }
    }
}
