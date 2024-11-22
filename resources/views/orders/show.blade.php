@extends('layout')

@section('content')
<h1>ℹ️ Order details</h1>

<section>
  <a href="{{ route('orders.index') }}" class="a-button">
    🔙 Back to orders
  </a>
</section>

<section>
  <p>
    <strong>Customer name</strong>: {{ $order->customer_name }}<br />
    <strong>Need by</strong>: {{ $order->need_by }}
  </p>
</section>

<section>
  <h2>Order items</h2>

  <div class="datatable-wrapper">
    <table id="order-items-table" class="display" style="width:100%">
      <thead>
        <tr>
          <th>Product name</th>
          <th>Quantity</th>
        </tr>
      </thead>
    </table>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
  $(document).ready(function() {
    // Order items table
    $('#order-items-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '{{ route("orders.items.data", ["id" => $order->id]) }}',
      },
      dom: 'rtip',
      columns: [{
          data: 'product_name',
          name: 'product_name'
        },
        {
          data: 'quantity',
          name: 'quantity'
        }
      ],
    });
  });
</script>
@endsection