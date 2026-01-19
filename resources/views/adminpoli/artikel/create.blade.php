@extends('layouts.adminpoli')

@section('title', 'Tambah Artikel')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/adminPoli/artikel.css') }}">
@endpush

@section('content')
<div class="artikel-page">

    <div class="artikel-topbar">
        <div class="artikel-left">
            <a href="{{ route('adminpoli.artikel.index') }}" class="artikel-back-img" title="Kembali">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
            </a>
            <div class="artikel-heading">Tambah Artikel</div>
        </div>

        <button type="submit" form="formArtikel" class="artikel-btn-add">
            <span>Simpan</span>
        </button>
    </div>

    <div class="artikel-card artikel-form-card">
        <form action="{{ route('adminpoli.artikel.store') }}" method="POST" enctype="multipart/form-data" id="formArtikel">
            @csrf

            <div class="artikel-form-grid">
                <div class="artikel-group">
                    <label>Judul Artikel</label>
                    <input type="text" name="judul_artikel" value="{{ old('judul_artikel') }}" required>
                </div>

                <div class="artikel-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>

                <div class="artikel-group artikel-group-full">
                    <label>Cover (1 gambar)</label>
                    <input type="file" name="cover" accept="image/*">
                    <small class="artikel-hint">Disimpan ke: <b>public/artikel-cover</b></small>
                </div>

                <div class="artikel-group artikel-group-full">
                    <label>Isi Artikel</label>
                    <textarea name="isi_artikel" rows="16" required>{{ old('isi_artikel') }}</textarea>
                    <small class="artikel-hint">
                        Kamu bisa pakai format sederhana (mis. awali paragraf dengan "##" untuk heading).
                    </small>
                </div>
            </div>
        </form>
    </div>

    <div class="artikel-foot">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>
</div>
@endsection
