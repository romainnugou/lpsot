<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LPSOT</title>
  <!-- <link href="{{ asset('css/app.css') }}" type="text/css" rel="stylesheet"> -->
  @vite('resources/css/app.css')
</head>

<body>
  <div class="page-container">
    <header>
      <a href="{{ route('schedule.index') }}" class="logo" title="Laravel Production Schedule Optimization Test">
        LPSOT
      </a>
      <nav>
        <a href="{{ route('schedule.index') }}">Schedule</a>
        <a href="{{ route('products.index') }}">Products</a>
        <a href="{{ route('orders.index') }}">Orders</a>
      </nav>
    </header>
    <div class="content">
      @yield('content')
    </div>
    <footer>
      <p>
        LPSOT stands for "Laravel Production Schedule Optimization Test" - Romain Nugou
      </p>
    </footer>
  </div>
</body>

</html>