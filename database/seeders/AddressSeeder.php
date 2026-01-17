<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAddress;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        // Connaught Place
        UserAddress::create([
            'user_id' => 2, // Customer
            'label' => 'Office',
            'address_line1' => 'F-Block, Inner Circle',
            'address_line2' => 'Connaught Place',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'latitude' => 28.6315,
            'longitude' => 77.2167,
            'is_default' => false,
        ]);

        // Hauz Khas Village
        UserAddress::create([
            'user_id' => 2,
            'label' => 'Hangout',
            'address_line1' => 'Building No 9, Hauz Khas Village',
            'address_line2' => 'Deer Park',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110016',
            'latitude' => 28.5532,
            'longitude' => 77.1944,
            'is_default' => false,
        ]);

        // Saket Select Citywalk
        UserAddress::create([
            'user_id' => 2,
            'label' => 'Mall',
            'address_line1' => 'Select Citywalk Mall',
            'address_line2' => 'District Centre, Saket',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110017',
            'latitude' => 28.5283,
            'longitude' => 77.2185,
            'is_default' => false,
        ]);
        
        // Karol Bagh
        UserAddress::create([
            'user_id' => 2,
            'label' => 'Home',
            'address_line1' => '12/45, WEA, Karol Bagh',
            'address_line2' => 'Near Metro Station',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110005',
            'latitude' => 28.6521,
            'longitude' => 77.1895,
            'is_default' => true,
        ]);
    }
}
