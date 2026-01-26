@extends('layouts.kepegawaian')

@section('title','Rincian Data Pegawai')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pegawai-detail.css') }}">
@endpush

@section('content')

<div class="detail-pegawai-container">

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <h4>Detail Pegawai</h4>

        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('pegawai.edit', $pegawai->nip) }}" class="btn btn-edit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
        </div>
    </div>


    {{-- PROFIL --}}
    <div class="profile-card">
        <div class="profile-cover"></div>

        <div class="profile-body">
            <div class="profile-avatar">
                @if(!empty($pegawai->foto))
                    {{-- Hapus 'storage/' karena file ada di public/profile-pegawai --}}
                    <img src="{{ asset('profile-pegawai/' . $pegawai->foto) }}" alt="Foto Pegawai">
                @else
                    <img src="{{ asset('assets/default-avatar.png') }}" alt="Avatar Default">
                @endif
            </div>

            <h2>{{ $pegawai->nama_pegawai }}</h2>
            <p class="profile-bagian">{{ $pegawai->bagian }}</p>

            <div class="profile-meta">
                <form action="{{ route('pegawai.update', $pegawai->nip) }}" method="POST" class="status-form-inline">
                    @csrf
                    <select name="is_active" onchange="this.form.submit()" class="form-select status-dropdown-inline {{ $pegawai->is_active ? 'status-active' : 'status-inactive' }}">
                        <option value="1" {{ $pegawai->is_active == 1 ? 'selected' : '' }}>🟢 Aktif</option>
                        <option value="0" {{ $pegawai->is_active == 0 ? 'selected' : '' }}>🔴 Non Aktif</option>
                    </select>
                </form>
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
            <div><label>Status Perkawinan</label><p>{{ $pegawai->status_pernikahan ?? '-' }}</p></div>
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
            <div>
                <label>Status</label>
                <p>
                    <span class="badge {{ $pegawai->is_active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $pegawai->is_active ? 'Aktif' : 'Non Aktif' }}
                    </span>
                </p>
            </div>
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
            <div><label>No. Telepon</label><p>{{ $pegawai->no_telp ?? '-' }}</p></div>
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
            <div><label>Tahun Lulus</label><p>{{ $pegawai->thn_lulus ?? '-' }}</p></div>
        </div>
    </div>

    
</div>

<script>
// Auto-hide alert after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s ease-out';
        alert.style.opacity = '0';
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 500);
    });
}, 5000);
</script>
@endsection