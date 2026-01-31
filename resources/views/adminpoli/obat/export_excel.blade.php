<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border:1px solid #000; padding:6px; }
        th { background:#eee; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>ID Obat</th>
                <th>Nama Obat</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->id_obat }}</td>
                    <td>{{ $row->nama_obat }}</td>
                    <td>{{ $row->harga }}</td>
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