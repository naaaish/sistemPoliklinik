@extends('layouts.kepegawaian')
@section('title','Dashboard')

@section('content')

<h2>Selamat Datang, Admin Kepegawaian!</h2>

<div class="stats">
    <div class="stat-box">87<br><small>Total Pasien</small></div>
    <div class="stat-box">6<br><small>Pemeriksaan Hari Ini</small></div>
    <div class="stat-box">46<br><small>Total Pegawai</small></div>
</div>

<table class="table">
<tr><th>Nama Pasien</th><th>Waktu</th><th>Dokter</th><th></th></tr>
<tr><td>Tyler Hyde</td><td>08:30</td><td>Dr. Crystal</td><td>▶</td></tr>
</table>

@endsection
