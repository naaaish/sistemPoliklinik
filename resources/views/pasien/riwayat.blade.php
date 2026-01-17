@extends('layouts.pasien')

@section('title','Riwayat Pemeriksaan')

@section('content')

<div class="riwayat">

    {{-- HERO --}}
    <section class="pasien-hero">
        <h1>Riwayat Pemeriksaan</h1>
    </section>

    <div class="pasien-container">

        {{-- ================= PROFIL PASIEN ================= --}}
        <div class="profile-section">
            <h2>Profil Pasien</h2>

            @if($pasien)
            {{-- DROPDOWN HUBUNGAN --}}
            <form method="GET" action="{{ route('pasien.riwayat') }}" class="pasien-switch">
                <label>Hubungan Keluarga</label>
                <select name="pasien_id" onchange="this.form.submit()">
                    @foreach($daftarPasien as $p)
                        <option value="{{ $p->id_pasien }}"
                            {{ $p->id_pasien == $pasienAktifId ? 'selected' : '' }}>
                            {{ ucfirst($p->hub_kel) }} - {{ $p->nama_pasien }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="profile-box">
                <div>
                    <p><b>Nama Pasien</b> {{ $pasien->nama_pasien }}</p>
                    <p><b>NIP</b> {{ $pegawai->nip ?? '-' }}</p>
                    <p><b>Nama Pegawai</b> {{ $pegawai->nama_pegawai ?? '-' }}</p>
                    <p><b>Bidang</b> {{ $pegawai->bidang ?? '-' }}</p>
                </div>

                <div>
                    <p><b>Hubungan</b> {{ ucfirst($pasien->hub_kel) }}</p>
                    <p><b>Tanggal Lahir</b>
                        {{ \Carbon\Carbon::parse($pasien->tgl_lahir)->translatedFormat('d F Y') }}
                    </p>
                    
                </div>
            </div>
            @else
                <div class="riwayat-empty">
                    <img src="{{ asset('images/empty-state.png') }}" alt="No Data" onerror="this.style.display='none'">
                    <h3>Data Pasien Belum Ada</h3>
                    <p>Silakan lengkapi data pasien terlebih dahulu.</p>
                </div>
            @endif
        </div>

        {{-- ================= RIWAYAT ================= --}}
        <div class="riwayat-grid">
            @forelse($riwayat as $i => $r)
            <div class="riwayat-card">
                <div class="riwayat-header">
                    <span>RIWAYAT {{ $i + 1 }}</span>
                    <span>
                        {{ \Carbon\Carbon::parse($r->created_at)
                            ->translatedFormat('l, d F Y, H:i') }}
                    </span>
                </div>

                <div class="riwayat-body">
                    <p><b>Dokter:</b> {{ $r->nama_dokter }}</p>
                    <p><b>Jenis Dokter:</b> {{ ucfirst($r->jenis_dokter) }}</p>
                    <p><b>Keluhan:</b> {{ $r->keluhan }}</p>

                    <a href="{{ route('pasien.pemeriksaan.detail', $r->id_pemeriksaan) }}" class="detail-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Lihat Detail
                    </a>
                </div>
            </div>
            @empty
                <div class="riwayat-empty">
                    <img src="{{ asset('images/empty-state.png') }}" alt="No Data" onerror="this.style.display='none'">
                    <h3>Tidak ada riwayat pemeriksaan</h3>
                    <p>Riwayat pemeriksaan Anda akan muncul di sini</p>
                </div>
            @endforelse
        </div>

    </div>
</div>

<style>
/* Dropdown Form */
.pasien-switch {
    margin-bottom: 30px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pasien-switch label {
    font-weight: 600;
    color: #316BA1;
    font-size: 15px;
}

.pasien-switch select {
    padding: 12px 16px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 15px;
    font-family: 'Poppins', sans-serif;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
}

.pasien-switch select:focus {
    outline: none;
    border-color: #316BA1;
}

/* Detail Button */
.detail-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    padding: 10px 20px;
    background: #316BA1;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}

.detail-btn:hover {
    background: #3f7fbf;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(49, 107, 161, 0.3);
}

.detail-btn svg {
    width: 18px;
    height: 18px;
}
</style>

@endsection