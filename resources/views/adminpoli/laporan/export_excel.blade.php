<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border:1px solid #000; padding:6px; font-size:12px; }
    th { background:#eee; }
  </style>
</head>
<body>
  <h3>LAPORAN KLINIK ({{ $from }} s/d {{ $to }})</h3>

  <table>
    <thead>
      <tr>
        <th>NO</th>
        <th>TANGGAL</th>
        <th>NAMA</th>
        <th>UMUR</th>
        <th>BAGIAN</th>
        <th>NAMA PASIEN</th>
        <th>HUB KEL</th>
        <th>TD</th>
        <th>GDP</th>
        <th>GD 2jam PP</th>
        <th>GDS</th>
        <th>AU</th>
        <th>CHOL</th>
        <th>TG</th>
        <th>Suhu</th>
        <th>BB</th>
        <th>TB</th>
        <th>DIAGNOSA</th>
        <th>TERAPHY</th>
        <th>JUMLAH OBAT</th>
        <th>HARGA OBAT (SATUAN)</th>
        <th>TOTAL HARGA OBAT</th>
        <th>PEMERIKSA</th>
        <th>NB</th>
        <th>PERIKSA KE :</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r['NO'] }}</td>
          <td>{{ $r['TANGGAL'] }}</td>
          <td>{{ $r['NAMA'] }}</td>
          <td>{{ $r['UMUR'] }}</td>
          <td>{{ $r['BAGIAN'] }}</td>
          <td>{{ $r['NAMA_PASIEN'] }}</td>
          <td>{{ $r['HUB_KEL'] }}</td>
          <td>{{ $r['TD'] }}</td>
          <td>{{ $r['GDP'] }}</td>
          <td>{{ $r['GD_2JAM_PP'] }}</td>
          <td>{{ $r['GDS'] }}</td>
          <td>{{ $r['AU'] }}</td>
          <td>{{ $r['CHOL'] }}</td>
          <td>{{ $r['TG'] }}</td>
          <td>{{ $r['SUHU'] }}</td>
          <td>{{ $r['BB'] }}</td>
          <td>{{ $r['TB'] }}</td>
          <td>{{ $r['DIAGNOSA'] }}</td>
          <td>{{ $r['TERAPHY'] }}</td>
          <td>{{ $r['JUMLAH_OBAT'] }}</td>
          <td>{{ $r['HARGA_OBAT_SATUAN'] }}</td>
          <td>{{ $r['TOTAL_HARGA_OBAT'] }}</td>
          <td>{{ $r['PEMERIKSA'] }}</td>
          <td>{{ $r['NB'] }}</td>
          <td>{{ $r['PERIKSA_KE'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="25">Tidak ada data pada rentang ini.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
