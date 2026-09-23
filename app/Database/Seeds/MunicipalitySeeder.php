<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MunicipalitySeeder extends Seeder
{
    public function run()
    {
        $file = APPPATH . 'Database/Seeds/municipalities.txt';

        $lines = file(
            $file,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );


        $data = [];

        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', trim($line), 2);

            if (count($parts) === 2) {
                $data[] = [
                    'province_id' => (int) $parts[0],
                    'municipality_name' => trim($parts[1]),
                ];
            }
        }

        $this->db->table('municipalities')->insertBatch($data);
    }
}
