<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Data\Timeline;
use Carbon\Carbon;

class ScheduleService
{
  private $startDate; // Production start date
  private $dayStartHour; // Production day start hour
  private $dayEndHour; // Production day end hour
  private $changeoverTime; // Time to change product type production (in minutes)
  private $startProductionDayWithSetupTime; // Do we start the production (on the first day) with a setup time
  private $timeline;

  // To save the best encountered queue
  private array $bestQueue = [];
  private int $bestDelayedOrdersCount = PHP_INT_MAX;
  private int $bestDelayedItemsCount = PHP_INT_MAX;

  public function __construct()
  {
    $this->startDate = now();
    $this->dayStartHour = config('schedule.day_start_hour');
    $this->dayEndHour = config('schedule.day_end_hour');
    $this->changeoverTime = config('schedule.changeover_time');
    $this->startProductionDayWithSetupTime = config('schedule.start_with_setup_time');
    $this->timeline = new Timeline();
  }

  /**
   * Generate production timeline
   */
  public function generateTimeline(): Timeline
  {
    // We get first queue
    $queue = $this->getOrderItemsQueue();
    // We init max iterations to the number of order items we have
    $maxIterations = count($queue);
    $iterations = 0;

    // We gonna simulate the timeline until we find a timeline where there's no delayed order
    do {
      [$delayedOrders, $delayedItems] = $this->simulateTimeline($queue);

      // We save the best queue (if it is)
      $this->saveBestQueue($queue, $delayedOrders, $delayedItems);

      // We stop there if there's no delayed order
      if (empty($delayedOrders)) {
        break;
      }

      // We reprioritize the queue (we move delayed order items at the beginning of the queue to try to simulate it another time)
      $queue = $this->reprioritizeQueue($queue, $delayedOrders);
      $iterations++;
    } while ($iterations < $maxIterations);

    // We use the best queue to build the timeline
    $this->buildTimelineFromQueue($this->bestQueue, $this->bestDelayedOrdersCount > 0);

    return $this->timeline;
  }

  /**
   * Get OrderItems queue
   */
  private function getOrderItemsQueue()
  {
    $orders = Order::with(['orderItems.product.productType'])
      ->orderBy('need_by')
      ->get();

    $queue = [];

    foreach ($orders as $order) {
      foreach ($order->orderItems as $item) {
        $queue[] = [
          'order' => $order,
          'orderItem' => $item,
          'productTypeId' => $item->product->productType->id,
          'productId' => $item->product->id,
        ];
      }
    }

    // Sort by product type and product
    usort($queue, function ($a, $b) {
      return $a['productTypeId'] <=> $b['productTypeId']
        ?: $a['productId'] <=> $b['productId'];
    });

    return $queue;
  }

  /**
   * Simulate timeline to check delayed orders
   */
  private function simulateTimeline(array $queue): array
  {
    $currentDateTime = $this->startDate
      ->copy()
      ->setHour($this->dayStartHour)
      ->setMinute(0);

    $endOfDay = $currentDateTime
      ->copy()
      ->setHour($this->dayEndHour)
      ->setMinute(0);

    $delayedOrders = [];
    $delayedItems = [];
    $lastProductType = null;

    foreach ($queue as $entry) {
      $order = $entry['order'];
      $orderItem = $entry['orderItem'];
      $productType = $orderItem->product->productType;
      $remainingQuantity = $orderItem->quantity;

      // Changeover time

      if (($this->startProductionDayWithSetupTime && is_null($lastProductType))
        || (!is_null($lastProductType) && $lastProductType->id !== $productType->id)
      ) {
        $currentDateTime->addMinutes($this->changeoverTime);
      }

      // Production

      while ($remainingQuantity > 0) {
        $todayAvailableTime = $currentDateTime->diffInMinutes($endOfDay, false);

        if ($todayAvailableTime <= 0) {
          $currentDateTime
            ->addDay()
            ->setHour($this->dayStartHour)
            ->setMinute(0);

          $endOfDay = $currentDateTime->copy()
            ->setHour($this->dayEndHour)
            ->setMinute(0);

          $todayAvailableTime = $currentDateTime->diffInMinutes($endOfDay, false);
        }

        $unitsPerMinute = $productType->units_per_hour / 60;
        $todayPossibleQuantity = floor($unitsPerMinute * $todayAvailableTime);

        if ($todayPossibleQuantity >= $remainingQuantity) {
          $productionTime = ceil($remainingQuantity / $unitsPerMinute);
          $currentDateTime->addMinutes($productionTime);
          $remainingQuantity = 0;
        } else {
          $productionTime = $todayAvailableTime;
          $currentDateTime->addMinutes($productionTime);
          $remainingQuantity -= floor($unitsPerMinute * $productionTime);
        }
      }

      // Check if order or item is delayed
      if ($currentDateTime->greaterThan(Carbon::parse($order->need_by))) {
        $delayedOrders[] = $order->id;
        $delayedItems[] = $orderItem->id;
      }

      $lastProductType = $productType;
    }

    return [$delayedOrders, $delayedItems];
  }

  /**
   * Reprioritize delayed orders in the queue
   */
  private function reprioritizeQueue(array $queue, array $delayedOrders): array
  {
    // We move delayed orders to the beginning of the queue
    $delayed = array_filter($queue, fn($entry) => in_array($entry['order']->id, $delayedOrders));
    $nonDelayed = array_filter($queue, fn($entry) => !in_array($entry['order']->id, $delayedOrders));

    return array_merge($delayed, $nonDelayed);
  }

  /**
   * Build final timeline from priotized queue
   */
  private function buildTimelineFromQueue(array $queue, bool $hasDelays)
  {
    $currentDateTime = $this->startDate
      ->copy()
      ->setHour($this->dayStartHour)
      ->setMinute(0);

    $endOfDay = $currentDateTime
      ->copy()
      ->setHour($this->dayEndHour)
      ->setMinute(0);

    $lastProductType = null;

    // We browse every order item we have in the queue
    foreach ($queue as $entry) {
      $order = $entry['order'];
      $orderItem = $entry['orderItem'];
      $productType = $orderItem->product->productType;
      $remainingQuantity = $orderItem->quantity;

      // Changeover time

      if (($this->startProductionDayWithSetupTime && is_null($lastProductType))
        || (!is_null($lastProductType) && $lastProductType->id !== $productType->id)
      ) {
        $this->timeline->addChangeoverEvent($currentDateTime, $this->changeoverTime, $productType->name);
        $currentDateTime->addMinutes($this->changeoverTime);
      }

      // Production

      while ($remainingQuantity > 0) {
        $todayAvailableTime = $currentDateTime->diffInMinutes($endOfDay, false);

        // If day's over, we switch to tomorrow
        if ($todayAvailableTime <= 0) {
          $currentDateTime
            ->addDay()
            ->setHour($this->dayStartHour)
            ->setMinute(0);

          $endOfDay = $currentDateTime->copy()
            ->setHour($this->dayEndHour)
            ->setMinute(0);

          $todayAvailableTime = $currentDateTime->diffInMinutes($endOfDay, false);
        }

        $unitsPerMinute = $productType->units_per_hour / 60;
        $todayPossibleQuantity = floor($unitsPerMinute * $todayAvailableTime);

        if ($todayPossibleQuantity >= $remainingQuantity) {
          // If we can finish this order item today

          $productionTime = ceil($remainingQuantity / $unitsPerMinute);
          $this->timeline->addProductionEvent($currentDateTime, $productionTime, $order, $orderItem->product, $remainingQuantity);
          $currentDateTime->addMinutes($productionTime);
          $remainingQuantity = 0;
        } else {
          // If we can't finish it today, we have to do the remaining tomorrow

          $productionTime = $todayAvailableTime;
          $this->timeline->addProductionEvent($currentDateTime, $productionTime, $order, $orderItem->product, floor($unitsPerMinute * $productionTime));
          $currentDateTime->addMinutes($productionTime);
          $remainingQuantity -= floor($unitsPerMinute * $productionTime);
        }
      }

      $lastProductType = $productType;
    }

    // We list delayed orders
    $this->timeline->listDelayedOrders();

    // We mark timeline as done
    $this->timeline->setSuccess(true);
  }


  /**
   * Save the best encountered queue
   */
  private function saveBestQueue(array $queue, array $delayedOrders, array $delayedItems): void
  {
    $currentDelayedOrdersCount = count(array_unique($delayedOrders));
    $currentDelayedItemsCount = count($delayedItems);

    if ($currentDelayedOrdersCount < $this->bestDelayedOrdersCount
      ||($currentDelayedOrdersCount === $this->bestDelayedOrdersCount && $currentDelayedItemsCount < $this->bestDelayedItemsCount)) {
      $this->bestQueue = $queue;
      $this->bestDelayedOrdersCount = $currentDelayedOrdersCount;
      $this->bestDelayedItemsCount = $currentDelayedItemsCount;
    }
  }
}
