@extends('layouts.kepegawaian')

@section('title','Rincian Data Pegawai')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kepegawaian/detail-pegawai.css') }}">
@endpush

@section('content')

<div class="detail-pegawai-container">

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="d-flex align-items-center">
            <a href="{{ route('kepegawaian.pegawai') }}" class="btn-back-icon me-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h4 class="mb-0">Detail Pegawai</h4>
        </div>

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

    {{-- PROFILE CARD  --}}
    <div class="profile-card-new">
        <div class="profile-left">

            <div class="profile-info">
                <h2>{{ $pegawai->nama_pegawai }}</h2>
                <p class="nip-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    NIP: {{ $pegawai->nip }}
                </p>
            </div>
        </div>

        <div class="profile-right">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Jabatan</span>
                    <span class="info-value">{{ $pegawai->jabatan ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Bagian</span>
                    <span class="info-value">{{ $pegawai->bagian }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Jenis Kelamin</span>
                    <span class="info-value">{{ $pegawai->jenis_kelamin ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Umur</span>
                    <span class="info-value">{{ $pegawai->tgl_lahir ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->age.' Tahun' : '-' }}</span>
                </div>
            </div>

            <div class="status-section">
                <span class="status-label">Status Pegawai:</span>
                <form action="{{ route('pegawai.update', $pegawai->nip) }}" method="POST" class="status-form-inline">
                    @csrf
                    @method('PUT')
                    <select name="is_active" onchange="this.form.submit()" class="status-dropdown-new {{ $pegawai->is_active ? 'status-active' : 'status-inactive' }}">
                        <option value="1" {{ $pegawai->is_active == 1 ? 'selected' : '' }}>🟢 Aktif</option>
                        <option value="0" {{ $pegawai->is_active == 0 ? 'selected' : '' }}>🔴 Non Aktif</option>
                    </select>
                </form>
            </div>
        </div>
    </div>

    {{-- DATA DETAIL - 2 COLUMNS --}}
    <div class="data-grid-2col">
        {{-- INFORMASI PRIBADI --}}
        <div class="data-card-new">
            <div class="card-header-new">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h3>Informasi Pribadi</h3>
            </div>
            <div class="card-body-new">
                <div class="detail-row">
                    <span class="detail-label">Tanggal Lahir</span>
                    <span class="detail-value">{{ $pegawai->tgl_lahir ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->translatedFormat('d F Y') : '-' }}</span>
                </div>
            </div>
        </div>

        {{-- KONTAK --}}
        <div class="data-card-new">
            <div class="card-header-new">
                <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <h3>Kontak</h3>
            </div>
            <div class="card-body-new">
                <div class="detail-row">
                    <span class="detail-label">No. Telepon</span>
                    <span class="detail-value">{{ $pegawai->no_telp ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $pegawai->email ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ALAMAT - FULL WIDTH --}}
    <div class="data-card-new">
        <div class="card-header-new">
            <div class="card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <h3>Alamat</h3>
        </div>
        <div class="card-body-new">
            <p class="alamat-text">{{ $pegawai->alamat ?? '-' }}</p>
        </div>
    </div>

    {{-- DAFTAR KELUARGA - TIDAK DIUBAH --}}
    <div class="data-card">
        <div class="card-header" style="background: #316BA1; display: flex; justify-content: space-between; align-items: center;">
            <div class="card-header-inner">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Daftar Keluarga</span>
            </div>
            <a href="{{ route('keluarga.create', $pegawai->nip) }}" class="btn-tambah">
                <span>+</span> Tambah Keluarga
            </a>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%">Hubungan</th>
                            <th style="width: 30%">Nama</th>
                            <th style="width: 20%">Tgl Lahir</th>
                            <th style="width: 20%">Jenis Kelamin</th>
                            <th style="width: 10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($keluarga as $k)
                        <tr style="{{ $k->is_active == 0 ? 'opacity: 0.5; background-color: #f8f9fa; color: #94a3b8;' : '' }}">
                            <td class="fw-bold text-secondary">
                                {{ ucfirst($k->hubungan_keluarga) }} 
                                @if($k->urutan_anak)
                                    <small class="d-block fw-normal text-muted">(Anak ke-{{ $k->urutan_anak }})</small>
                                @endif
                            </td>
                            <td>{{ $k->nama_keluarga }}</td>
                            <td>{{ \Carbon\Carbon::parse($k->tgl_lahir)->translatedFormat('d F Y') }}</td>
                            <td>{{ $k->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="text-center">
                                <a href="{{ route('keluarga.edit', $k->id_keluarga) }}" class="btn btn-edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data keluarga terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Toast notification untuk success/error messages
@if(session('success'))
    window.AdminPoliToast.fire({
        icon: 'success',
        title: '{{ session("success") }}'
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '{{ session("error") }}',
        confirmButtonColor: '#316BA1'
    });
@endif
</script>
@endsection