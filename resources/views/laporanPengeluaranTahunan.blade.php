<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Pengeluaran Tahunan</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha/css/bootstrap.css" rel="stylesheet">
</head>

<body>
    <img src="HeaderStruk.jpg" width="680px">
    <br><br>
    <h1 class="center marginTop">Laporan Pengeluaran Tahunan</h1>
    <p>Tahun: {{$from}} s/d {{$to}}
    </p>

    <table>
        <tr>
          <th>No</th>
          <th>Tahun</th>
          <th>Makanan</th>
          <th>Side Dish</th>
          <th>Minuman</th>
          <th>Total Pengeluaran</th>
        </tr>
    
        @foreach($years as $year)
         <tr>
          <td>{{ ++$no }}</td>
          <td>{{ $year }}</td>
          @php
          //mendapatkan index jika ada $year dalam array $tahunBayar
          $found = array_search($year, $tahunBayar);
          @endphp
          @if($found !== false)
          <td>@currency($data[$found]->makanan_utama)</td>
          <td>@currency($data[$found]->makanan_side_dish)</td>
          <td>@currency($data[$found]->minuman)</td>
          <td>@currency($totalPengeluaran[$found]->total_pengeluaran)</td>
          @else
          <td>-</td>
          <td>-</td>
          <td>-</td>
          <td>-</td>
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