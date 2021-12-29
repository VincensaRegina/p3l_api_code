<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Penjualan Item Menu</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha/css/bootstrap.css" rel="stylesheet">
</head>

<body>
  <img src="HeaderStruk.jpg" width="680px">
  <br><br>
  <h1 class="center marginTop">Laporan Penjualan Item Menu</h1>
  <p>Tahun: {{$tahun}}
    <!-- ambil date dari incomingStock dan parse it into month year. ex: Mei -->
    <br> Bulan: {{$bulan}}
  </p>

  @foreach($jenis as $j)
  <h6> {{$j}} </h6>

  @php
  $no = 1;
  @endphp

  <table>
    <tr>
      <th>No</th>
      <th>Item Menu</th>
      <th>Unit</th>
      <th>Penjualan Harian Tertinggi</th>
      <th>Total Penjualan</th>
    </tr>
    @foreach($data as $d)
    @if($d->jenis == $j)<tr>
      <td>{{ $no++ }}</td>
      <td>{{ $d->nama_menu}}</td>
      <td>{{ $d->unit }}</td>
      <td>{{ $d->penjualan_harian_tertinggi }}</td>
      <td>{{ $d->total_penjualan }}</td>
    </tr>
    @endif
    @endforeach
  </table>
  <br>
  @endforeach

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