<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <title>LPSOT</title>
  <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
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