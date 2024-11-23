<?php

namespace App\Services\Data;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

class Timeline
{
  private array $events = [];
  private bool $success = false;
  private array $delayedOrders = [];

  /**
   * Get timeline array for controller
   */
  public function getTimelineArray(): array
  {
    $formattedEvents = [];
    foreach ($this->events as $date => $dayEvents) {
      $formattedEvents[$date] = array_map(fn($event) => $event->toArray(), $dayEvents);
    }

    return $formattedEvents;
  }

  /**
   * Has events
   */
  public function hasEvents(): bool
  {
    return count($this->events) > 0;
  }

  /**
   * Is timeline built
   */
  public function isSuccess(): bool
  {
    return $this->success;
  }

  /**
   * Set timeline success
   */
  public function setSuccess(bool $value)
  {
    $this->success = $value;
  }

  /**
   * Add changeover event
   */
  public function addChangeoverEvent(Carbon $startTime, int $duration, string $productTypeName)
  {
    $this->addEvent(
      $startTime,
      $duration,
      "Changeover time: change to {$productTypeName}",
      "changeover",
      null,
      null,
      null,
      null,
    );
  }

  /**
   * Add production event
   */
  public function addProductionEvent(Carbon $startTime, int $duration, Order $order, Product $product, int $quantity)
  {
    $delayed = $startTime->copy()->startOfDay()->gt(Carbon::parse($order->need_by)->startOfDay());

    $this->addEvent(
      $startTime,
      $duration,
      "Production of {$quantity} {$product->name} ({$product->productType->name})",
      "production",
      $order->id,
      $order->customer_name,
      $order->need_by,
      $delayed
    );
  }

  /**
   * Add event to timeline
   */
  private function addEvent(Carbon $startTime, int $duration, string $description, string $type, int|null $orderId, string|null $orderCustomerName, string|null $orderNeedBy, bool|null $delayed)
  {
    $endTime = $startTime
      ->copy()
      ->addMinutes($duration);
    $date = $startTime
      ->toDateString();

    if (!isset($this->events[$date])) {
      $this->events[$date] = [];
    }

    $this->events[$date][] = new Event($startTime->copy(), $endTime, $description, $type, $orderId, $orderCustomerName, $orderNeedBy, $delayed);
  }

  /**
   * List delayed orders
   */
  public function listDelayedOrders()
  {
    foreach($this->events as $date => $events) {
      foreach($events as $event) {
        if(!is_null($event->delayed) && $event->delayed == true) {
          array_push($this->delayedOrders, $event->order_id);
        }
      }
    }
  }

  /**
   * Get delayed orders
   */
  public function getDelayedOrders()
  {
    return Order::findOrFail($this->delayedOrders);
  }
}
