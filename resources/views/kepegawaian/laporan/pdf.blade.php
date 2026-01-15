<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans; font-size: 12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #000; padding:6px; }
        th { background:#eee; }
    </style>
</head>
<body>

<h3>{{ $judul }}</h3>

<table>
    <thead>
        <tr>
            <th>Nama Pasien</th>
            <th>Dokter</th>
            <th>Tanggal</th>
            <th>Keluhan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
        <tr>
            <td>{{ $d->nama_pasien }}</td>
            <td>{{ $d->nama_dokter ?? '-' }}</td>
            <td>{{ $d->tanggal }}</td>
            <td>{{ $d->keluhan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
