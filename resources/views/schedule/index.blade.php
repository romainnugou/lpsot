@extends('layout')

@section('content')
<h1>📆 Schedule</h1>

@if(!$success)
<p class="warning">
  ❗ An error occured while building the production schedule.
</p>
@elseif(!$hasEvents)
<p class="warning">
  ❗ No orders found. Add some orders to build a schedule.
</p>
@else
@if($delayedOrders->count() > 0)
<div class="warning">
  <p>
  ❗ Some orders are delayed:
  </p>
  <ul>
  @foreach($delayedOrders as $delayedOrder)
    <li><a href="{{ route('orders.show', $delayedOrder->id) }}">Order #{{ $delayedOrder->id }}</a> for {{ $delayedOrder->customer_name }} needed by {{ $delayedOrder->need_by }}</li>
  @endforeach
  </ul>
  <p>
    Order items delayed are marked with a ❌.
  </p>
</div>
@endif

<div class="schedule-container">
  @foreach($timelineArr as $date => $events)
  <div class="schedule-day">
    <div class="schedule-date">
      {{ $date }}
    </div>
    <div class="schedule-events">
    @foreach($events as $event)
      <div class="schedule-event {{ $event['type'] }}">
        <div class="event-times">
          <div class="event-times-from">
            <span class="from">From</span>
            <span class="from-time">{{ $event['start'] }}</span>
          </div>
          <div class="event-times-to">
            <span class="to">To</span>
            <span class="to-time">{{ $event['end'] }}</span>
          </div>
        </div>
        <div class="event-infos">
          @if(!is_null($event['order_id']) && !is_null($event['order_customer_name']))
            <div class="event-order-info">
              <a href="{{ route('orders.show', $event['order_id']) }}">Order #{{ $event['order_id'] }}</a> for {{ $event['order_customer_name'] }} needed by {{ $event['order_need_by'] }}
              @if($event['delayed'])
                ❌
              @else
                ✔️
              @endif
            </div>
          @endif
          <div class="event-description">
            {{ $event['description'] }}
          </div>
        </div>
      </div>
    @endforeach
    </div>
  </div>
  @endforeach
</div>
@endif
@endsection