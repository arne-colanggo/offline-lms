<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run()
    {
        //
        $data = [
            [
                'region_name' => 'NCR',
                'region_description' => 'National Capital Region',
            ],
            [
                'region_name' => 'CAR',
                'region_description' => 'Cordillera Administrative Region',
            ],
            [
                'region_name' => 'Region I',
                'region_description' => 'Ilocos Region',
            ],
            [
                'region_name' => 'Region II',
                'region_description' => 'Cagayan Valley',
            ],
            [
                'region_name' => 'Region III',
                'region_description' => 'Central Luzon',
            ],
            [
                'region_name' => 'Region IV-A',
                'region_description' => 'CALABARZON',
            ],
            [
                'region_name' => 'Region IV-B',
                'region_description' => 'MIMAROPA',
            ],
            [
                'region_name' => 'Region V',
                'region_description' => 'Bicol Region',
            ],
            [
                'region_name' => 'Region VI',
                'region_description' => 'Western Visayas',
            ],
            [
                'region_name' => 'Region VII',
                'region_description' => 'Central Visayas',
            ],
            [
                'region_name' => 'Region VIII',
                'region_description' => 'Eastern Visayas',
            ],
            [
                'region_name' => 'Region IX',
                'region_description' => 'Zamboanga Peninsula',
            ],
            [
                'region_name' => 'Region X',
                'region_description' => 'Northern Mindanao',
            ],
            [
                'region_name' => 'Region XI',
                'region_description' => 'Davao Region',
            ],
            [
                'region_name' => 'Region XII',
                'region_description' => 'Davao Region',
            ],
            [
                'region_name' => 'Region XIII',
                'region_description' => 'CARAGA',
            ],
            [
                'region_name' => 'BARMM',
                'region_description' => 'Bangsamoro Autonomous Region in Muslim Mindanao',
            ],
        ];

        $this->db->table('regions')->insertBatch($data);
    }
}
