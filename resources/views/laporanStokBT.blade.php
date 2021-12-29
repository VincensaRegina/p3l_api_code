<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Stok Bulan Tahun</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha/css/bootstrap.css" rel="stylesheet">
</head>

<body>
  <img src="HeaderStruk.jpg" width="680px">
  <br><br>
  <h1 class="center marginTop">Laporan Stok Bahan</h1>
  <p>Item Menu: {{$menu['nama']}}
    <!-- ambil date dari incomingStock dan parse it into month year. ex: Mei 2021 -->
    <br> Periode: {{$bulanTahun}}
  </p>

  <table>
    <tr>
      <th>No</th>
      <th>Tanggal</th>
      <th>Unit</th>
      <th>Incoming Stock</th>
      <th>Remaining Stock</th>
      <th>Waste Stock</th>
    </tr>

    @foreach($dates as $date)
    <tr>
      <td>{{ ++$no }}</td>
      <td>{{ $date }}</td>
      <td>{{ $menu['unit'] }}</td>
      @php
      $found = array_search($date, $dateRS);
      @endphp
      @if($found !== false)
      <td>{{ $incomingStock[$found]->incoming_stock }}</td>
      <td>{{ $incomingStock[$found]->remaining_stock }}</td>
      <td>{{ $wasteStock[$found]->waste_stock }}</td>
      @else
      <td>0</td>
      <td>0</td>
      <td>0</td>
      @endif
      @endforeach
    </tr>
  </table>
  <br>
  <p class="center printed">Printed {{\Carbon\Carbon::now()->format('M j, Y H:i:s A') }}<br>Printed by {{$karyawan}}</p>
</body>

</html>
<style>
  body {
    font-family: arial, sans-serif;
    font-size: 14px;
  }

  .center {
    text-align: center;
    font-size: 20px;
  }

  .marginTop {
    margin-top: -45px;
  }

  table {

    border-collapse: collapse;
    width: 100%;
  }

  td,
  th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
  }

  .printed {
    font-size: 14px;
  }

  .centeredImage {
    padding-left: 10px;
  }
</style>