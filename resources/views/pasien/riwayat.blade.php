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

            @if($keluargaAktif)

                {{-- DROPDOWN PILIH KELUARGA --}}
                <div class="pasien-switch-wrapper">
                    <form method="GET" action="{{ route('pasien.riwayat') }}" class="pasien-switch-form">
                        <div class="switch-box">
                            <label><i class="fas fa-users"></i> Hubungan Keluarga</label>
                            <select name="id_keluarga" onchange="this.form.submit()">
                                @foreach($daftarKeluarga as $k)
                                    <option value="{{ $k->id_keluarga }}" {{ $k->id_keluarga == $keluargaAktifId ? 'selected' : '' }}>
                                        @if($k->hubungan_keluarga === 'pegawai')
                                            Pegawai (YBS)
                                        @elseif($k->hubungan_keluarga === 'anak')
                                            Anak ke-{{ $k->urutan_anak }}
                                        @else
                                            {{ ucfirst($k->hubungan_keluarga) }}
                                        @endif
                                        - {{ $k->nama_keluarga }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                {{-- BOX PROFIL --}}
                <div class="profile-box">
                    <div>
                        <p>
                            <b>Nama Pasien:</b>
                            {{ $keluargaAktif->nama_keluarga ?? $pegawai->nama_pegawai }}
                        </p>

                        <p>
                            <b>Hubungan:</b>
                            {{ ucfirst($keluargaAktif->hubungan_keluarga) }}
                        </p>

                        <p>
                            <b>Tanggal Lahir:</b>
                            {{ \Carbon\Carbon::parse($keluargaAktif->tgl_lahir)->translatedFormat('d F Y') }}
                        </p>
                    </div>

                    <div>
                        <p><b>NIP:</b> {{ $pegawai->nip ?? '-' }}</p>
                        <p><b>Nama Pegawai:</b> {{ $pegawai->nama_pegawai ?? '-' }}</p>
                        <p><b>Bagian:</b> {{ $pegawai->bagian ?? '-' }}</p>
                    </div>
                </div>

            @else
                {{-- EMPTY STATE PROFIL --}}
                <div class="riwayat-empty">
                    <img src="{{ asset('images/empty-state.png') }}" alt="No Data"
                         onerror="this.style.display='none'">
                    <h3>Data Pasien Belum Ada</h3>
                    <p>Silakan lengkapi data keluarga terlebih dahulu.</p>
                </div>
            @endif
        </div>

        {{-- GRID RIWAYAT --}}
        <div class="riwayat-grid-modern">
            @forelse($riwayat as $i => $r)
                <div class="riwayat-card-modern">
                    <div class="card-header-blue">
                        <span class="riwayat-number">RIWAYAT {{ $i + 1 }}</span>
                        <span class="riwayat-date">{{ \Carbon\Carbon::parse($r->created_at)->translatedFormat('l, d F Y') }}</span>
                    </div>
                    <div class="card-body-modern">
                        <div class="info-row">
                            <div class="info-icon-circle">
                                <img src="{{ asset('assets/adminPoli/dokter.png') }}" alt="Icon Dokter" class="icon-img-dokter">
                            </div>
                            <div class="info-text">
                                <span class="text-name">{{ $r->nama_pemeriksa }}</span>
                                <span class="text-sub">Pemeriksa / Dokter</span>
                            </div>
                        </div>
                        <div class="divider-thin"></div>
                        <div class="info-row">
                            <div class="info-icon cyan"><i class="fas fa-notes-medical"></i></div>
                            <div class="info-text">
                                <span class="text-label">Keluhan</span>
                                <p class="text-value">{{ $r->keluhan ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon blue"><i class="fas fa-stethoscope"></i></div>
                            <div class="info-text">
                                <span class="text-label">Diagnosa Dokter</span>
                                <p class="text-value">{{ $r->daftar_diagnosa ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="card-footer-modern">
                            <a href="{{ route('pasien.pemeriksaan.detail', $r->id_pemeriksaan) }}" class="btn-detail-full">
                                Lihat Detail Lengkap →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="riwayat-empty">Belum ada riwayat pemeriksaan.</div>
            @endforelse
        </div>

    </div>
</div>


@endsection
