@extends('layouts.kepegawaian')

@section('content')
<h2>Data Pegawai</h2>

<div class="table-box">
<table>
    <thead>
        <tr>
            <th>NIP</th>
            <th>Nama Pegawai</th>
            <th>Jabatan</th>
            <th>Bidang</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pegawai as $p)
        <tr>
            <td>{{ $p->nip }}</td>
            <td>{{ $p->nama_pegawai }}</td>
            <td>{{ $p->jabatan }}</td>
            <td>{{ $p->bidang }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4">Belum ada data pegawai</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection
