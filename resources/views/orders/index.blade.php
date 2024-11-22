@extends('layout')

@section('content')
<h1>🗂️ Orders</h1>

<section>
  <p class="center">
    <a href="{{ route('orders.create') }}" class="a-button">✨ New order</a>
  </p>
</section>

<section>
  <div class="datatable-wrapper">
    <table id="orders-table" class="display" style="width:100%">
      <thead>
        <tr>
          <th>Created at</th>
          <th>Customer name</th>
          <th>Need by</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
  $(document).ready(function() {
    // Orders table
    $('#orders-table').DataTable({
      processing: true,
      serverSide: true,
      order: [2,'asc'],
      ajax: {
        url: '{{ route("orders.data") }}',
      },
      dom: 'rtip',
      columns: [{
          data: 'created_at',
          name: 'created_at'
        },
        {
          data: 'customer_name',
          name: 'customer_name'
        },
        {
          data: 'need_by',
          name: 'need_by'
        },
        {
          data: 'actions',
          name: 'actions',
          orderable: false,
          searchable: false
        },
      ],
    });

    $('#orders-table').on('click', '.delete-order', function() {
      const orderId = $(this).data('id');

      if (confirm('Are you sure you want to delete this order?')) {
        $.ajax({
          url: '{{ route("orders.delete", ":id") }}'.replace(':id', orderId),
          type: 'DELETE',
          data: {
            _token: '{{ csrf_token() }}',
          },
          success: function(response) {
            $('#orders-table').DataTable().ajax.reload();
          },
          error: function() {
            alert('An error occurred while deleting the order.');
          },
        });
      }
    });
  });
</script>
@endsection