<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\ProductTypeService;

class ProductsController extends Controller
{
    protected $productService;
    protected $productTypeService;

    public function __construct(ProductService $productService, ProductTypeService $productTypeService)
    {
        $this->productService = $productService;
        $this->productTypeService = $productTypeService;
    }

    public function index()
    {
        return view('products.index');
    }

    public function getProducts()
    {
        return $this->productService->getProductsForDataTable();
    }

    public function getProductTypes()
    {
        return $this->productTypeService->getProductTypesForDataTable();
    }
}
