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
    <h3>Data Saran ({{ $from }} s/d {{ $to }})</h3>

    <table>
        <thead>
            <tr>
                <th>ID Saran</th>
                <th>Saran</th>
                <th>ID Diagnosa</th>
                <th>Diagnosa</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->id_saran }}</td>
                    <td>{{ $row->saran }}</td>
                    <td>{{ $row->id_diagnosa }}</td>
                    <td>{{ $row->diagnosa_text }}</td>
                    <td>{{ $row->created_at }}</td>
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
