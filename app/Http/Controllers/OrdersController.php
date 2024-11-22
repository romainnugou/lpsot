<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrdersService;
use App\Services\ProductService;

class OrdersController extends Controller
{
    protected $ordersService;
    protected $productService;

    public function __construct(OrdersService $ordersService, ProductService $productService)
    {
        $this->ordersService = $ordersService;
        $this->productService = $productService;
    }

    /**
     * Orders page
     */
    public function index()
    {
        return view('orders.index');
    }

    /**
     * Orders for datatable
     */
    public function getOrders()
    {
        return $this->ordersService->getOrdersForDataTable();
    }

    /**
     * Order details page
     */
    public function show($id)
    {
        $order = $this->ordersService->getOrder($id);

        return view('orders.show')
            ->with(compact('order'));
    }

    /**
     * Order items for datatable
     */
    public function getOrderItems($id)
    {
        return $this->ordersService->getOrderItemsForDatatable($id);
    }

    /**
     * Create order page
     */
    public function create()
    {
        $products = $this->productService->getProducts();
        $productTypes = $this->productService->getProductTypes();

        return view('orders.create')
            ->with(compact('products', 'productTypes'));
    }

    /**
     * Order store action
     */
    public function store(StoreOrderRequest $request)
    {
        $order = $this->ordersService->createOrder($request->validated());

        return redirect()->route('orders.show', ['id' => $order->id]);
    }

    /**
     * Order delete action
     */
    public function delete($id)
    {
        return $this->ordersService->delete($id);
    }
}
