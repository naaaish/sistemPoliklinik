@extends('layouts.adminpoli')

@section('title', 'Dashboard Admin Poliklinik')

@section('content')
<div class="ap-page">

    <h1 class="ap-title">Dashboard</h1>
    {{-- Cards --}}
    <div class="ap-cards">
        <div class="ap-card">
            <img class="ap-card__icon" src="{{ asset('assets/adminPoli/people.png') }}" alt="kunjungan">
            <div class="ap-card__value">{{ $kunjunganHariIni }}</div>
            <div class="ap-card__label">Kunjungan Hari Ini</div>
        </div>

        <div class="ap-card ap-card--alert ap-card--center">
            <img class="ap-card__icon" src="{{ asset('assets/adminPoli/datablminput.png') }}" alt="belum input">
            <div class="ap-card__value ap-card__value--big">{{ $belumDiinput }}</div>
            <div class="ap-card__label ap-card__label--big">Hasil Pemeriksaan Pasien<br/>Belum Diinput</div>
        </div>

        <div class="ap-card">
            <img class="ap-card__icon" src="{{ asset('assets/adminPoli/people.png') }}" alt="total pasien">
            <div class="ap-card__value">{{ $totalPasienBulanIni }}</div>
            <div class="ap-card__label">Total Pasien Bulan Ini</div>
        </div>
    </div>

    {{-- Button input pendaftaran --}}
    <a href="{{ route('adminpoli.pendaftaran.create') }}" class="ap-banner">
        <img class="ap-banner__icon" src="{{ asset('assets/adminPoli/input.png') }}" alt="input">
        <div class="ap-banner__text">Input Pendaftaran Pasien</div>
    </a>

    {{-- Table pasien aktif --}}
    <section class="ap-section">
        <div class="ap-section__title">Daftar Pasien Aktif</div>

        <div class="ap-table-wrap">
            <table class="ap-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Tanggal Periksa</th>
                        <th class="ap-th-center">Input Hasil</th>
                        <th class="ap-th-center">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
                @forelse($daftarPasienAktif as $row)
                    <tr>
                        <td>{{ $row->nama_pasien }}</td>
                        <td>{{ $row->nip ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}</td>
                        <td class="ap-td-center">
                            <a class="ap-input-btn" href="{{ route('adminpoli.pemeriksaan.create', $row->id_pendaftaran) }}">
                                <img src="{{ asset('assets/adminPoli/masuk.png') }}" alt="input hasil">
                            </a>
                        </td>
                        <td class="ap-td-center">
                            <form method="POST"
                                    action="{{ route('adminpoli.pendaftaran.destroy', $row->id_pendaftaran) }}"
                                    onsubmit="return confirm('Yakin hapus pendaftaran ini?')"
                                    class="ap-action-form">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="ap-icon-btn ap-icon-btn--danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="ap-empty">Tidak ada pasien aktif hari ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <footer class="ap-footer">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </footer>

</div>
@endsection
