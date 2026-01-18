@extends('layouts.kepegawaian')

@section('title', 'Rincian Data Pegawai')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/detail-pegawai.css') }}">
@endpush

@section('content')

<div class="detail-pegawai-container">
    
    {{-- HEADER --}}
    <div class="detail-pegawai-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="37" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <h1>Rincian Data Pegawai</h1>
    </div>

    {{-- CARD HEADER PROFIL --}}
    <div class="profil-card">
        <div class="profil-header">
            <div class="profil-avatar">
                <img src="{{ asset('assets/default-avatar.png') }}" alt="Avatar">
            </div>
            <div class="profil-info">
                <h2>{{ $pegawai->nama_pegawai }}</h2>
                <p class="profil-bidang">{{ $pegawai->bidang }}</p>
                <div class="profil-meta">
                    <span class="status-badge status-aktif">Aktif</span>
                    <span class="nip-text">NIP : {{ $pegawai->nip }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION DATA PRIBADI --}}
    <div class="data-section">
        <div class="section-header" onclick="toggleSection('pribadi')">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <h3>Data Pribadi</h3>
            </div>
            <button class="toggle-btn" id="toggle-pribadi">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>
        <div class="section-content" id="content-pribadi" style="display: none;">
            <div class="data-grid-two">
                <div class="data-field">
                    <label>NAMA LENGKAP :</label>
                    <p>{{ $pegawai->nama_pegawai }}</p>
                </div>
                <div class="data-field">
                    <label>NIK :</label>
                    <p>{{ $pegawai->nik ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>JENIS KELAMIN :</label>
                    <p>{{ $pegawai->jenis_kelamin ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>TEMPAT LAHIR :</label>
                    <p>{{ $pegawai->tempat_lahir ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>TANGGAL LAHIR :</label>
                    <p>{{ $pegawai->tgl_lahir ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->translatedFormat('d F Y') : '-' }}</p>
                </div>
                <div class="data-field">
                    <label>UMUR :</label>
                    <p>{{ $pegawai->tgl_lahir ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->age . ' Tahun' : '-' }}</p>
                </div>
                <div class="data-field">
                    <label>AGAMA :</label>
                    <p>{{ $pegawai->agama ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>STATUS PERKAWINAN :</label>
                    <p>{{ $pegawai->status_perkawinan ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION DATA KEPEGAWAIAN --}}
    <div class="data-section">
        <div class="section-header section-header-alt" onclick="toggleSection('kepegawaian')">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <h3>Data Kepegawaian</h3>
            </div>
            <button class="toggle-btn" id="toggle-kepegawaian">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>
        <div class="section-content" id="content-kepegawaian" style="display: none;">
            <div class="data-grid-two">
                <div class="data-field">
                    <label>NIP :</label>
                    <p>{{ $pegawai->nip }}</p>
                </div>
                <div class="data-field">
                    <label>JABATAN :</label>
                    <p>{{ $pegawai->jabatan ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>BIDANG :</label>
                    <p>{{ $pegawai->bidang }}</p>
                </div>
                <div class="data-field">
                    <label>STATUS KEPEGAWAIAN :</label>
                    <p>{{ $pegawai->status ?? 'Aktif' }}</p>
                </div>
                <div class="data-field">
                    <label>TANGGAL MASUK :</label>
                    <p>{{ $pegawai->tgl_masuk ? \Carbon\Carbon::parse($pegawai->tgl_masuk)->translatedFormat('d F Y') : '-' }}</p>
                </div>
                <div class="data-field">
                    <label>MASSA KERJA :</label>
                    <p>{{ $pegawai->tgl_masuk ? \Carbon\Carbon::parse($pegawai->tgl_masuk)->diffForHumans(null, true) : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION KONTAK DAN ALAMAT --}}
    <div class="data-section">
        <div class="section-header" onclick="toggleSection('kontak')">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <h3>Kontak dan Alamat</h3>
            </div>
            <button class="toggle-btn" id="toggle-kontak">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>
        <div class="section-content" id="content-kontak" style="display: none;">
            <div class="data-grid-single">
                <div class="data-field">
                    <label>NO. TELEPON :</label>
                    <p>{{ $pegawai->no_telepon ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>EMAIL :</label>
                    <p>{{ $pegawai->email ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>ALAMAT LENGKAP :</label>
                    <p>{{ $pegawai->alamat ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION RIWAYAT PENDIDIKAN --}}
    <div class="data-section">
        <div class="section-header section-header-alt" onclick="toggleSection('pendidikan')">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
                <h3>Riwayat Pendidikan</h3>
            </div>
            <button class="toggle-btn" id="toggle-pendidikan">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>
        <div class="section-content" id="content-pendidikan" style="display: none;">
            <div class="data-grid-single">
                <div class="data-field">
                    <label>PENDIDIKAN TERAKHIR :</label>
                    <p>{{ $pegawai->pendidikan_terakhir ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>INSTITUSI :</label>
                    <p>{{ $pegawai->institusi ?? '-' }}</p>
                </div>
                <div class="data-field">
                    <label>TAHUN LULUS :</label>
                    <p>{{ $pegawai->tahun_lulus ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function toggleSection(section) {
    const content = document.getElementById('content-' + section);
    const toggleBtn = document.getElementById('toggle-' + section);
    const svgLine = toggleBtn.querySelector('svg line:first-child');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        svgLine.style.display = 'none'; // Hide vertical line (make it minus)
    } else {
        content.style.display = 'none';
        svgLine.style.display = 'block'; // Show vertical line (make it plus)
    }
}
</script>
@endpush

@endsection