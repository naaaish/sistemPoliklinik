@extends('pasien.layout')
@section('title','Riwayat Pemeriksaan')

@section('content')

<div class="pasien riwayat">

    {{-- HERO --}}
    <div class="pasien-hero">
        <div class="kembali" onclick="window.history.back()">
            ← Kembali
        </div>
        <h1>Riwayat<br>Pemeriksaan</h1>
    </div>

    <div class="pasien-container">

        {{-- PROFIL PASIEN --}}
        <div class="profile-section">
            <h2>Profil Pasien</h2>

            <div class="profile-box">
                <div class="col">
                    <p><b>Nama Pasien :</b> {{ data_get($pasien,'nama_pasien','-') }}</p>
                    <p><b>NIP :</b> {{ data_get($pasien,'nip','-') }}</p>
                    <p><b>Nama Pegawai :</b> {{ data_get($pasien,'pegawai.nama','-') }}</p>
                    <p><b>Bidang :</b> {{ data_get($pasien,'pegawai.bidang','-') }}</p>
                </div>

                <div class="col">
                    <p><b>Hubungan Keluarga :</b> {{ data_get($pasien,'hub_kel','-') }}</p>
                    <p><b>Tanggal Lahir :</b> {{ data_get($pasien,'tgl_lahir','-') }}</p>
                    <p><b>Jenis Kelamin :</b> {{ data_get($pasien,'jenis_kelamin','-') }}</p>
                    <p><b>Umur :</b> {{ data_get($pasien,'umur','-') }}</p>
                </div>
            </div>
        </div>

        {{-- RIWAYAT --}}
        <div class="riwayat-grid">

            @forelse($riwayat as $item)

                <div class="riwayat-card">
                    <div class="riwayat-header">
                        <span>RIWAYAT</span>
                        <span>{{ $item->tanggal }}</span>
                    </div>

                    <div class="riwayat-body">
                        <h4>{{ $item->dokter_nama }}</h4>

                        <p><b>Keluhan:</b> {{ $item->keluhan }}</p>
                        <p><b>Diagnosa:</b> {{ $item->diagnosa }}</p>
                        <p><b>Resep:</b> {{ $item->resep }}</p>

                        <a href="#" class="detail-btn">
                            Lihat Detail Lengkap →
                        </a>
                    </div>
                </div>

            @empty

                {{-- EMPTY STATE --}}
                <div class="riwayat-empty">
                    <img src="{{ asset('images/empty.png') }}" alt="Kosong">
                    <h3>Belum ada riwayat pemeriksaan</h3>
                    <p>Riwayat akan muncul setelah Anda melakukan pemeriksaan di poliklinik.</p>
                </div>

            @endforelse

        </div>

    </div>
</div>

@endsection
