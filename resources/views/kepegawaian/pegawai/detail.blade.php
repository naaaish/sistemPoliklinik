@extends('layouts.kepegawaian')

@section('title','Rincian Data Pegawai')

@section('content')
<div class="detail-pegawai-container">

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <a href="{{ url()->previous() }}" class="btn-back">←</a>
        <h1>Rincian Data Pegawai</h1>
    </div>

    {{-- PROFIL --}}
    <div class="profile-card">
        <div class="profile-cover"></div>

        <div class="profile-body">
            <div class="profile-avatar">
                <img src="{{ asset('assets/default-avatar.png') }}" alt="Avatar">
            </div>

            <h2>{{ $pegawai->nama_pegawai }}</h2>
            <p class="profile-bagian">{{ $pegawai->bagian }}</p>

            <div class="profile-meta">
                <span class="badge badge-active">{{ $pegawai->status ?? 'Aktif' }}</span>
                <span class="nip">NIP : {{ $pegawai->nip }}</span>
            </div>
        </div>
    </div>

    {{-- DATA PRIBADI --}}
    <div class="data-card">
        <div class="card-header">
            <div class="card-header-inner">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
                <span>Data Pribadi</span>
            </div>
        </div>
        <div class="card-body grid-2">
            <div><label>Nama Lengkap</label><p>{{ $pegawai->nama_pegawai }}</p></div>
            <div><label>NIK</label><p>{{ $pegawai->nik ?? '-' }}</p></div>
            <div><label>Jenis Kelamin</label><p>{{ $pegawai->jenis_kelamin ?? '-' }}</p></div>
            <div><label>Tempat Lahir</label><p>{{ $pegawai->tempat_lahir ?? '-' }}</p></div>
            <div><label>Tanggal Lahir</label><p>{{ $pegawai->tgl_lahir ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->translatedFormat('d F Y') : '-' }}</p></div>
            <div><label>Umur</label><p>{{ $pegawai->tgl_lahir ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->age.' Tahun' : '-' }}</p></div>
            <div><label>Agama</label><p>{{ $pegawai->agama ?? '-' }}</p></div>
            <div><label>Status Perkawinan</label><p>{{ $pegawai->status_perkawinan ?? '-' }}</p></div>
        </div>
    </div>

    {{-- DATA KEPEGAWAIAN --}}
    <div class="data-card">
        <div class="card-header alt">
            <div class="card-header-inner">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M9 4v18M15 4v18"/></svg>
                <span>Data Kepegawaian</span>
            </div>
        </div>
        <div class="card-body grid-2">
            <div><label>NIP</label><p>{{ $pegawai->nip }}</p></div>
            <div><label>Jabatan</label><p>{{ $pegawai->jabatan ?? '-' }}</p></div>
            <div><label>Bagian</label><p>{{ $pegawai->bagian }}</p></div>
            <div><label>Status</label><p>{{ $pegawai->status ?? 'Aktif' }}</p></div>
            <div><label>Tanggal Masuk</label><p>{{ $pegawai->tgl_masuk ? \Carbon\Carbon::parse($pegawai->tgl_masuk)->translatedFormat('d F Y') : '-' }}</p></div>
            <div>
                <label>Masa Kerja</label>
                <p>{{ (int) $years }} Tahun {{ (int) $months }} Bulan</p>
            </div>


        </div>
    </div>

    {{-- KONTAK --}}
    <div class="data-card">
        <div class="card-header">
            <div class="card-header-inner">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Kontak & Alamat</span>
            </div>
        </div>
        <div class="card-body grid-1">
            <div><label>No. Telepon</label><p>{{ $pegawai->no_telepon ?? '-' }}</p></div>
            <div><label>Email</label><p>{{ $pegawai->email ?? '-' }}</p></div>
            <div><label>Alamat</label><p>{{ $pegawai->alamat ?? '-' }}</p></div>
        </div>
    </div>

    {{-- RIWAYAT PENDIDIKAN --}}
    <div class="data-card">
        <div class="card-header alt">
            <div class="card-header-inner">
                <svg viewBox="0 0 24 24"><path d="M22 10L12 4 2 10l10 6z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                <span>Riwayat Pendidikan</span>
            </div>
        </div>
        <div class="card-body grid-1">
            <div><label>Pendidikan Terakhir</label><p>{{ $pegawai->pendidikan_terakhir ?? '-' }}</p></div>
            <div><label>Institusi</label><p>{{ $pegawai->institusi ?? '-' }}</p></div>
            <div><label>Tahun Lulus</label><p>{{ $pegawai->tahun_lulus ?? '-' }}</p></div>
        </div>
    </div>

</div>
@endsection
