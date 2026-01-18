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
                <th style="text-align: center;">Lihat</th> 
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai as $p)
            <tr>
                <td>{{ $p->nip }}</td>
                <td>{{ $p->nama_pegawai }}</td>
                <td>{{ $p->jabatan }}</td>
                <td>{{ $p->bidang }}</td>
                <td>
                    <a href="{{ route('kepegawaian.pegawai.show', $p->nip) }}" class="view-btn">+</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5">Belum ada data pegawai</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@endsection
