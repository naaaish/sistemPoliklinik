@extends('layouts.kepegawaian')

@section('title','Rincian Data Pegawai')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pegawai-detail.css') }}">
@endpush

@section('content')

<div class="detail-pegawai-container">

    {{-- PAGE HEADER --}}
    <div class="page-header">
        {{-- Sisi Kiri: Icon Back + Judul --}}
        <div class="d-flex align-items-center">
            <a href="{{ route('kepegawaian.pegawai') }}" class="btn-back-icon me-3">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Back" style="width: 38px; height: 38px;">
            </a>
            <h4 class="mb-0">Detail Pegawai</h4>
        </div>

        {{-- Sisi Kanan: Tombol Edit --}}
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
    
    {{-- DAFTAR KELUARGA --}}
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
            {{-- Tombol Tambah Keluarga yang lebih rapi --}}
            <a href="{{ route('keluarga.create', $pegawai->nip) }}" class="btn-tambah-sm">
                <span>+</span> Tambah Keluarga
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
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
                        {{-- LOGIC BUREM: Jika is_active adalah 0 --}}
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
                                <a href="{{ route('keluarga.edit', $k->id_keluarga) }}" class="btn-edit-sm">
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