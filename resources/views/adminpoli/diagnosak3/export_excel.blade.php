<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: Arial, sans-serif; }
    h3 { margin: 0 0 10px 0; color:#3B5E8C; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border:1px solid #CFE0FF; padding:6px; }
    th { background:#E7F0FF; color:#3B5E8C; text-align:left; }
    .cat { background:#F3F8FF; font-weight:700; }
    .muted { color:#7B8DA8; }
  </style>
</head>
<body>
  <h3>Data Diagnosa K3</h3>

  <table>
    <thead>
      <tr>
        <th>Tipe</th>
        <th>ID NB</th>
        <th>Kategori</th>
        <th>Nama Penyakit</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr class="{{ $r->tipe === 'kategori' ? 'cat' : '' }}">
          <td>{{ $r->tipe }}</td>
          <td>{{ $r->id_nb }}</td>
          <td>{{ $r->kategori_penyakit }}</td>
          <td>{{ $r->tipe === 'kategori' ? '—' : $r->nama_penyakit }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="4">Tidak ada data.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
