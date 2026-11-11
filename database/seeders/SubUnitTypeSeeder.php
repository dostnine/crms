<?php

namespace Database\Seeders;

use App\Models\SubUnitType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubUnitTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sub_unit_types = [


            // 
            [
                'sub_unit_id' => 3,
                'region_id'=> 10,
                'type_name' => 'APL (RO)',
            ],
            
            [
                'sub_unit_id' => 3,
                'region_id'=> 10,
                'type_name' => 'LDC (RO)',
            ],
            [
                'sub_unit_id' => 3,
                'region_id'=> 10,
                'type_name' => 'RRD (PSTO-ZDN)',
            ],
            [
                'sub_unit_id' => 3,
                'region_id'=> 10,
                'type_name' => 'AAF (PSTO-ZDS)',
            ],
            [
                'sub_unit_id' => 3,
                'region_id'=> 10,
                'type_name' => 'CGB (PSTO-ZS)',
            ],
            [
                'sub_unit_id' => 3,
                'region_id'=> 10,
                'type_name' => 'ATP (CSTC-ZCIC)',
            ],
            [
                'sub_unit_id' => 3,
                'region_id'=> 10,
                'type_name' => 'JRE (CSTC-ZCIC)',
            ],
        ];

        SubUnitType::insert($sub_unit_types);
    }
}
