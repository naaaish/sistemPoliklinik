@extends('layouts.adminpoli')

@section('title', 'Edit Artikel')

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
            <div class="artikel-heading">Edit Artikel</div>
        </div>

        <button type="submit" form="formArtikel" class="artikel-btn-add">
            <span>Simpan</span>
        </button>
    </div>

    <div class="artikel-card artikel-form-card">
        <form action="{{ route('adminpoli.artikel.update', $artikel->id_artikel) }}"
              method="POST"
              enctype="multipart/form-data"
              id="formArtikel">
            @csrf
            @method('PUT')

            <div class="artikel-form-grid">
                <div class="artikel-group">
                    <label>Judul Artikel</label>
                    <input type="text" name="judul_artikel" value="{{ old('judul_artikel', $artikel->judul_artikel) }}" required>
                </div>

                <div class="artikel-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', $artikel->tanggal) }}" required>
                </div>

                <div class="artikel-group artikel-group-full">
                    <label>Cover (1 gambar)</label>

                    <div class="artikel-cover-preview">
                        <img src="{{ asset($artikel->cover_path ?? 'artikel-cover/default.png') }}"
                             alt="cover">
                        <div class="artikel-cover-meta">
                            <div class="artikel-hint">Cover saat ini</div>
                            <div class="artikel-hint"><b>{{ $artikel->cover_path }}</b></div>
                        </div>
                    </div>

                    <input type="file" name="cover" accept="image/*">
                    <small class="artikel-hint">Upload baru kalau mau ganti cover.</small>
                </div>

                <div class="artikel-group artikel-group-full">
                    <label>Isi Artikel</label>
                    <textarea name="isi_artikel" rows="18" required>{{ old('isi_artikel', $artikel->isi_artikel) }}</textarea>
                </div>
            </div>
        </form>
    </div>

    <div class="artikel-foot">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>
</div>
@endsection
