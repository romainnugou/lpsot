<?php

namespace App\Services;

use App\Models\ProductType;
use Yajra\DataTables\DataTables;

class ProductTypeService
{
  public function getProductTypesForDataTable()
  {
    return DataTables::of(ProductType::query())
      ->make(true);
  }
}
