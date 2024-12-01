{{--
  Construido con trabajo duro y mucho café por el equipo de
  Droni.co. Do you want to build something amazing? We're hiring
  https://droni.co
  ©2023 Colombia
--}}<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registros de Actualizaciones | GPS Alarma CYJ</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous">
  </head>
  <body>
    <div class="container">
      <form action="/siggotrack.php" method="POST" class="float-end" target="_blank">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="key" value="3143390071">
        <input type="date" class="form-control" id="from_date" name="from_date" value="{{ date('Y-m-d') }}">
        <input type="submit" class="btn btn-primary" value="Actualizar Registros">
      </form>
      <h1>Siigo Logs</h1>
      <form action="" method="GET" class="row g-3">
        <div class="col-md-3">
          <label for="from_date" class="form-label">From date</label>
          <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $from_date }}">
        </div>
        <div class="col-md-3">
          <label for="to_date" class="form-label">To date</label>
          <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $to_date }}">
        </div>
        <div class="col-2">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>

      </form>
      <table class="table tablte-striped">
        <caption>List of Siigo Logs</caption>
        <thead>
          <tr>
            <th>Id</th>
            <th>User</th>
            <th>Siigo Id</th>
            <th>Cuenti Id</th>
            <th>Subscription</th>
            <th>Code</th>
            <th>Quantity</th>
            <th>Seller</th>
            <th>Timestamps</th>
          </tr>
          @foreach($siigologs as $siigolog)
          <tr>
            <td>{{ $siigolog->id }}</td>
            <td>
              {{ $siigolog->user ? $siigolog->user->email : ' no user' }}

            </td>
            <td>
              {{ $siigolog->siigo_id }}
            </td>
            <td>
              {{ $siigolog->cuenti_id }}
            </td>
            <td>
              {{ $siigolog->subscription_expiration_old }}<br>
              {{ $siigolog->subscription_expiration }}
            </td>
            <td>{{ $siigolog->code }}</td>
            <td>{{ $siigolog->quantity }}</td>
            <td>
              {{ $siigolog->seller }}<br>
              {{ $siigolog->seller_name }}
            </td>
            <td>
              {{ $siigolog->created_at }}<br>
              {{ $siigolog->updated_at }}
            </td>
          </tr>
          @endforeach
        </thead>
      </table>
      {{ $siigologs->links() }}
      <footer>
        <hr>
        <small>
          GPS Alarma CYJ - Centro Multiservicios
        </small>
      </footer>
    </div>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"></script>
  </body>
</html>
