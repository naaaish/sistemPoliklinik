@extends('layouts.adminpoli')

@section('title', 'Dashboard Admin Poliklinik')

@section('content')
<div class="ap-page">

    <h1 class="ap-title">Dashboard</h1>
    @if(session('success'))
        <div class="ap-alert ap-alert--success">
         {{ session('success') }}
        </div>
    @endif 


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
                    </tr>
                </thead>
                <tbody>
                @forelse($daftarPasienAktif as $row)
                    <tr>
                        <td>{{ $row->nama_pasien }}</td>
                        <td>{{ $row->nip ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}</td>
                        <td class="ap-td-center">
                            <a class="ap-input-btn" href="#">
                                <img src="{{ asset('assets/adminPoli/masuk.png') }}" alt="input hasil">
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="ap-empty">Tidak ada pasien aktif hari ini.</td>
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
