<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductType;
use PDO;
use Yajra\DataTables\DataTables;

class ProductService
{
  public function getProductsForDataTable()
  {
    return DataTables::of(
      Product::query()
        ->leftJoin('product_types', 'products.product_type_id', '=', 'product_types.id')
        ->select([
          'products.*',
          'product_types.name as product_type_name',
        ])
    )->make(true);
  }

  public function getProducts()
  {
    return Product::all();
  }

  public function getProductTypes()
  {
    return ProductType::all();
  }
}
