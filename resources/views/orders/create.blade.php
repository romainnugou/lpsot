@extends('layout')

@section('content')
<h1>✨ New order</h1>

<section>
  <a href="{{ route('orders.index') }}" class="a-button">
    🔙 Back to orders
  </a>
</section>

<section>
  <form action="{{ route('orders.store') }}" method="POST">
    @csrf

    <div class="form-container bordered-form-container">
      <h3>Order info</h3>

      <div class="form-group">
        <label for="customer_name">Customer name</label>
        <input type="text" 
          name="customer_name" 
          id="customer_name" 
          required />
      </div>

      <div class="form-group">
        <label for="need_by">Need by</label>
        <input type="date"
        name="need_by"
        id="need_by"
        min="{{ now()->toDateString() }}"
        required />
      </div>
    </div>

    <div class="form-container bordered-form-container">
      <h3>Order items</h3>

      <p class="warning">
        ❗ Every product you chose for this order must be of the same type. If you want multiple types of product you must order separately. Thank you.
      </p>

      <table id="order-items">
        <thead>
          <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="product">
              <select name="order_items[0][product_id]" class="product-select" id="first-product-select" required>
                @foreach($productTypes as $productType)
                <optgroup label="{{ $productType->name }}">
                  @foreach($productType->products as $product)
                  <option value="{{ $product->id }}" data-product-group-id="{{ $productType->id }}">{{ $product->name }}</option>
                  @endforeach
                </optgroup>
                @endforeach
              </select>
            </td>
            <td class="quantity">
              <input type="number"
                name="order_items[0][quantity]"
                min="1"
                max="2147483647"
                required />
            </td>
            <td class="actions">
            </td>
          </tr>
        </tbody>
      </table>

      <button type="button" id="add-item" class="small-button">🪄 Add item</button>
    </div>

    <div class="form-container center">
      <button type="submit">✨ Create order</button>
    </div>
  </form>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    const products = {!! json_encode($products) !!};
    const productTypes = {!! json_encode($productTypes) !!};

    let selectedProductType = -1;
    let itemIndex = 1;
    setSelectedProductType();

    // When the first product select change
    $('#first-product-select').change(function() {
      setSelectedProductType();
    });

    // Add order item line
    $('#add-item').click(function() {
      const row = `
        <tr>
          <td class="product">
            <select name="order_items[${ itemIndex }][product_id]" class="product-select" required>
              @foreach($productTypes as $productType)
              <optgroup label="{{ $productType->name }}">
                @foreach($productType->products as $product)
                <option value="{{ $product->id }}" data-product-group-id="{{ $productType->id }}">{{ $product->name }}</option>
                @endforeach
              </optgroup>
              @endforeach
            </select>
          </td>
          <td class="quantity">
            <input type="number"
              name="order_items[${ itemIndex }][quantity]"
              min="1"
              max="2147483647"
              required />
          </td>
          <td class="actions">
            <button type="button" class="remove-row small-button">🗑️ Remove</button>
          </td>
        </tr>
      `;
      $('#order-items tbody')
        .append(row)
        .ready(function() {
          disableUnavailableOptionsOnLastSelect();
        });
      itemIndex++;
    });

    // Remove an item row
    $(document).on('click', '.remove-row', function() {
      $(this).closest('tr').remove();

      if($('.product-select').length <= 1) {
        // If just the first product select -> we enable every options
        $('.product-select#first-product-select').each(function() {
        $(this).children().children().each(function() {;
          $(this).attr('disabled', false);
        });
      });
      }
    });

    // Get selected product type and browse selects to enable or disable options
    function setSelectedProductType() {
      const firstProductId = $('#first-product-select').val();

      products.forEach(product => {
        if(product.id == firstProductId) {
          selectedProductType = product.product_type_id;
          return false;
        }
      });

      $('.product-select:not(#first-product-select)').each(function() {
        $(this).children().children().each(function() {;
          $(this).attr('disabled', $(this).attr('data-product-group-id') != selectedProductType);
        });
      });
    }

    // Browse newly created select to disable unavailable options
    function disableUnavailableOptionsOnLastSelect() {
      let firstSelected = false;

      // Disable unavailable options on new select
      $('.product-select').last().children().children().each(function() {
        $(this).attr('disabled', $(this).attr('data-product-group-id') != selectedProductType);

        if(!firstSelected && $(this).attr('data-product-group-id') == selectedProductType) {
          $(this).prop('selected', true);
          firstSelected = true;
        }
      });

      // Disable unavailable options on first select
      $('.product-select#first-product-select').each(function() {
        $(this).children().children().each(function() {;
          $(this).attr('disabled', $(this).attr('data-product-group-id') != selectedProductType);
        });
      });
    }
  });
</script>
@endsection