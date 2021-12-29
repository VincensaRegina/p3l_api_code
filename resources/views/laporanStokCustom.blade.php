<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Stok Custom</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha/css/bootstrap.css" rel="stylesheet">
</head>

<body>
    <img src="HeaderStruk.jpg" width="680px">
    <br><br>
    <h1 class="center marginTop">Laporan Stok Bahan</h1>
    <p>Item Menu: ALL
      <!-- ambil date dari incomingStock dan parse it into month year. ex: Mei 2021 -->
      <br> Periode: CUSTOM ({{\Carbon\Carbon::parse($from)->translatedFormat('j M Y')}} s/d {{\Carbon\Carbon::parse($to)->translatedFormat('j M Y')}})
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
      <th>Incoming Stock</th>
      <th>Remaining Stock</th>
      <th>Waste Stock</th>
    </tr>
    @for ($i = 0; $i < count($incomingStock); $i++)
    @if($incomingStock[$i]->jenis_menu == $j)<tr>
      <td>{{ $no++ }}</td>
      <td>{{ $incomingStock[$i]->nama_bahan}}</td>
      <td>{{ $incomingStock[$i]->unit }}</td>
      <td>{{ $incomingStock[$i]->incoming_stock }}</td>
      <td>{{ $rs[$i]->remaining_stock }}</td>
      @if($wasteStock[$i]->waste_stock == 0)
      <td>-</td>
      @else
      <td>{{$wasteStock[$i]->waste_stock}}</td>
      @endif
    </tr>
    @endif
    @endfor
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