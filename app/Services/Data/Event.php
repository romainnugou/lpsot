<?php

namespace App\Services\Data;

use Carbon\Carbon;

class Event
{
  public Carbon $start;
  public Carbon $end;
  public string $description;
  public string $type;
  public int|null $order_id;
  public string|null $order_customer_name;
  public string|null $order_need_by;
  public bool|null $delayed;

  public function __construct(Carbon $start, Carbon $end, string $description, string $type, int|null $orderId, string|null $orderCustomerName, string|null $orderNeedBy, bool|null $delayed)
  {
    $this->start = $start;
    $this->end = $end;
    $this->description = $description;
    $this->type = $type;
    $this->order_id = $orderId;
    $this->order_customer_name = $orderCustomerName;
    $this->order_need_by = $orderNeedBy;
    $this->delayed = $delayed;
  }

  public function toArray(): array
  {
    return [
      'start' => $this->start->format('H:i'),
      'end' => $this->end->format('H:i'),
      'description' => $this->description,
      'type' => $this->type,
      'order_id' => $this->order_id,
      'order_customer_name' => $this->order_customer_name,
      'order_need_by' => $this->order_need_by,
      'delayed' => $this->delayed,
    ];
  }
}
