<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h3 { margin: 0 0 10px 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border:1px solid #000; padding:6px; }
        th { background:#eee; }
    </style>
</head>
<body>
    <h3>Data Saran</h3>

    <table>
        <thead>
            <tr>
                <th>ID Saran</th>
                <th>Saran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->id_saran }}</td>
                    <td>{{ $row->saran }}</td>
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
