@extends('layout')

@section('content')
<h1>📖 Products and types</h1>

<section>
  <h2>Products</h2>
  
  <div class="datatable-wrapper">
    <table id="products-table" class="display" style="width:100%">
      <thead>
        <tr>
          <th>Name</th>
          <th>Product type</th>
        </tr>
      </thead>
    </table>
  </div>
</section>

<section>
  <h2>Product types</h2>

  <div class="datatable-wrapper">
    <table id="product-types-table" class="display" style="width:100%">
      <thead>
        <tr>
          <th>Name</th>
          <th>Units/hour</th>
        </tr>
      </thead>
    </table>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
  $(document).ready(function() {
    // Products table
    $('#products-table').DataTable({
      processing: true,
      serverSide: true,
      order: [0,'asc'],
      ajax: '{{ route("products.data") }}',
      dom: 'rtip',
      columns: [{
          data: 'name',
          name: 'name'
        },
        {
          data: 'product_type_name',
          name: 'product_type_name'
        },
      ],
    });

    // Product types table
    $('#product-types-table').DataTable({
      processing: true,
      serverSide: true,
      order: [0,'asc'],
      ajax: '{{ route("products.types.data") }}',
      dom: 'rtip',
      columns: [{
          data: 'name',
          name: 'name'
        },
        {
          data: 'units_per_hour',
          name: 'units_per_hour'
        },
      ],
    });
  });
</script>
@endsection