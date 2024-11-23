<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductType;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $type1 = ProductType::where('name', 'Type 1')->first();
        $type2 = ProductType::where('name', 'Type 2')->first();
        $type3 = ProductType::where('name', 'Type 3')->first();

        $products = [
            ['name' => 'Product A', 'product_type_id' => $type1->id],
            ['name' => 'Product B', 'product_type_id' => $type1->id],
            ['name' => 'Product C', 'product_type_id' => $type2->id],
            ['name' => 'Product D', 'product_type_id' => $type3->id],
            ['name' => 'Product E', 'product_type_id' => $type3->id],
            ['name' => 'Product F', 'product_type_id' => $type1->id],
        ];

        if($type1 && $type2 && $type3) {
            foreach ($products as $product) {
                Product::firstOrCreate(
                    ['name' => $product['name']],
                    ['product_type_id' => $product['product_type_id']]
                );
            }
        } else {
            $this->command->warn('Product types not found.');
        }
    }
}
