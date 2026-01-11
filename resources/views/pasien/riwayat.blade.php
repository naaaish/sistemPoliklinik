@extends('layouts.pasien')
@section('title','Riwayat Pemeriksaan')

@section('content')

<section class="page-header">
    <h2>Riwayat Pemeriksaan</h2>
</section>

<section class="profile-box">
    <div class="profile-grid">
        <div>
            <b>Nama</b><br>
            Tyler Hyde
        </div>
        <div>
            <b>No. Pasien</b><br>
            24012025
        </div>
        <div>
            <b>Tanggal Lahir</b><br>
            12 September 2000
        </div>
        <div>
            <b>Jenis Kelamin</b><br>
            Perempuan
        </div>
    </div>
</section>

<section class="riwayat-list">

    <div class="riwayat-card">
        <div>
            <h4>08 Januari 2026</h4>
            <p>Demam, Batuk, Flu</p>
        </div>
        <a href="/pasien/pemeriksaan" class="btn">Detail</a>
    </div>

    <div class="riwayat-card">
        <div>
            <h4>02 Januari 2026</h4>
            <p>Sakit Kepala, Pusing</p>
        </div>
        <a href="/pasien/pemeriksaan" class="btn">Detail</a>
    </div>

</section>

@endsection
