<?php

namespace App\Services;

use Yajra\DataTables\DataTables;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class OrdersService
{
  public function getOrdersForDataTable()
  {
    return DataTables::of(Order::query())
      ->editColumn('created_at', function ($data) {
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('Y-m-d H:i');
        return $formatedDate;
      })
      ->addColumn('actions', function ($order) {
        return '
          <a href="' . route('orders.show', $order->id) . '" class="a-button a-small-button">ℹ️ Details</a>
          <button class="small-button delete-order" data-id="' . $order->id . '">🗑️ Delete</button>
        ';
      })
      ->rawColumns(['actions'])
      ->make(true);
  }

  public function getOrder($id)
  {
    return Order::findOrFail($id);
  }

  public function getOrderItemsForDatatable($orderId)
  {
    return DataTables::of(OrderItem::query()->where('order_id', $orderId))
      ->addColumn('product_name', function ($orderItem) {
        return $orderItem->product ? $orderItem->product->name : '';
      })
      ->make(true);
  }

  public function createOrder(array $data)
  {
    // Create order
    $order = Order::create([
      'customer_name' => $data['customer_name'],
      'need_by' => $data['need_by'],
    ]);

    // Create order items
    foreach ($data['order_items'] as $item) {
      OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $item['product_id'],
        'quantity' => $item['quantity'],
      ]);
    }

    return $order;
  }

  public function delete($orderId)
  {
    $order = Order::findOrFail($orderId);
    $order->delete();

    return response()->json(['message' => 'Order deleted successfully.'], 200);
  }
}
