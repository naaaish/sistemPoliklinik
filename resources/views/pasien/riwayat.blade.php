@extends('layouts.pasien')

@section('title','Riwayat Pemeriksaan')

@section('content')

<div class="riwayat">

    {{-- HERO RIWAYAT --}}
    <section class="pasien-hero">
        {{-- <a href="{{ url()->previous() }}" class="kembali">← Kembali</a> --}}
        <h1>Riwayat Pemeriksaan</h1>
    </section>

    <div class="pasien-container">

        {{-- PROFIL --}}
        {{-- <form method="GET" action="{{ route('pasien.riwayat') }}">
            <select name="pasien_id" onchange="this.form.submit()">
                @foreach($daftarPasien as $p)
                    <option value="{{ $p->id_pasien }}"
                        {{ $pasien && $pasien->id_pasien == $p->id_pasien ? 'selected' : '' }}>
                        {{ $p->nama_pasien }} ({{ ucfirst($p->hub_kel) }})
                    </option>
                @endforeach
            </select>
        </form> --}}


        <div class="profile-section">
            <h2>Profil Pasien</h2>

            <div class="profile-box">

            @if($pasien)
                <div>
                    <p><b>Nama Pasien</b>: {{ $pasien->nama_pasien }}</p>
                    <p><b>NIP</b>: {{ $pegawai->nip ?? '-' }}</p>
                    <p><b>Nama Pegawai</b>: {{ $pegawai->nama_pegawai ?? '-' }}</p>
                    <p><b>Bidang</b>: {{ $pegawai->bidang ?? '-' }}</p>
                </div>

                <div>
                    <p><b>Hubungan Keluarga</b>: {{ ucfirst($pasien->hub_kel) }}</p>
                    <p><b>Tanggal Lahir</b>: 
                        {{ \Carbon\Carbon::parse($pasien->tgl_lahir)->translatedFormat('d F Y') }}
                    </p>
                    <p><b>Jenis Kelamin</b>: {{ $pasien->jenis_kelamin }}</p>
                </div>
            @else
                <div class="riwayat-empty">
                    <h3>Data Pasien Belum Ada</h3>
                    <p>Silakan lengkapi data pasien terlebih dahulu.</p>
                </div>
            @endif

            </div>
        </div>

        {{-- RIWAYAT --}}
        <div class="riwayat-grid">

            @forelse($riwayat as $i => $r)
            <div class="riwayat-card">
                <div class="riwayat-header">
                    <span>RIWAYAT {{ $i+1 }}</span>
                    <span>{{ $r->created_at->translatedFormat('l, d M Y, H:i') }}</span>
                </div>

                <div class="riwayat-body">
                    <p><b>Dokter</b>: {{ $r->dokter }}</p>
                    <p><b>Keluhan</b>: {{ $r->keluhan }}</p>
                    <p><b>Diagnosa</b>: {{ $r->diagnosa }}</p>
                    <p><b>Resep</b>:
                        @foreach($r->resep as $d)
                            {{ $d->obat }} {{ $d->satuan }},
                        @endforeach
                    </p>

                    <a href="{{ route('pasien.riwayat.detail',$r->id_pemeriksaan) }}"
                    class="detail-btn">
                    Lihat Detail Lengkap →
                    </a>
                </div>
            </div>
            @empty
                <div class="riwayat-empty">
                    <h3>Tidak ada riwayat pemeriksaan</h3>
                </div>
            @endforelse

        </div>
</div>

@endsection
