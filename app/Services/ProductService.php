<?php

namespace App\Services;

use App\Models\Product;
use Yajra\DataTables\DataTables;

class ProductService
{
  public function getProductsForDataTable()
  {
    return DataTables::of(Product::query())
      ->addColumn('product_type_name', function ($product) {
        return $product->productType ? $product->productType->name : '';
      })
      ->make(true);
  }
}
