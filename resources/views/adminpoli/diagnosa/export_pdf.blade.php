<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border:1px solid #000; padding:6px; vertical-align: top; }
    th { background:#eee; }
  </style>
</head>
<body>
  <h3>Data Diagnosa</h3>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Diagnosa</th>
        <th>Keterangan</th>
        <th>Klasifikasi Nama</th>
        <th>Bagian Tubuh</th>
      </tr>
    </thead>
    <tbody>
      @forelse($data as $row)
        <tr>
          <td>{{ $row->id_diagnosa }}</td>
          <td>{{ $row->diagnosa }}</td>
          <td>{{ $row->keterangan }}</td>
          <td>{{ $row->klasifikasi_nama }}</td>
          <td>{{ $row->bagian_tubuh }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="5">Tidak ada data pada rentang ini.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>