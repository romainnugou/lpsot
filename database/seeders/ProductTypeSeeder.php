<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productTypes = [
            ['name' => 'Type 1', 'units_per_hour' => 715],
            ['name' => 'Type 2', 'units_per_hour' => 770],
            ['name' => 'Type 3', 'units_per_hour' => 1000],
        ];

        foreach ($productTypes as $type) {
            ProductType::firstOrCreate(
                ['name' => $type['name']],
                ['units_per_hour' => $type['units_per_hour']]
            );
        }
    }
}
