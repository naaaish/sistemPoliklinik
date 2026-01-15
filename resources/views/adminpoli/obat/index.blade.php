@extends('layouts.adminpoli')

@section('title', 'Kelola Obat')

@section('content')
<div class="ap-page">

    <h1 class="ap-title">Obat</h1>

    <section class="ap-section">
        <div class="ap-card ap-card--table">

            {{-- Header --}}
            <div class="ap-table__header">
                <form method="GET" action="{{ route('adminpoli.obat.index') }}" class="ap-search">
                    <input 
                        type="text" 
                        name="q" 
                        class="ap-search__input"
                        placeholder="Masukkan nama obat yang dicari"
                        value="{{ request('q') }}"
                    >
                    <button class="ap-btn ap-btn--primary" type="submit">
                        Cari
                    </button>
                </form>

                <a href="{{ route('adminpoli.obat.create') }}" class="ap-btn ap-btn--primary">
                    + Tambah
                </a>
            </div>

            {{-- Table --}}
            <table class="ap-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Harga Satuan</th>
                        <th>Exp Date</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($obat as $item)
                        <tr>
                            <td>{{ $item->nama_obat }}</td>
                            <td>Rp{{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->exp_date)->format('d-m-Y') }}</td>
                            <td class="ap-actions">
                                <a href="{{ route('adminpoli.obat.edit', $item->id_obat) }}" class="ap-link ap-link--edit">
                                    Edit
                                </a>
                                <form action="{{ route('adminpoli.obat.destroy', $item->id_obat) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ap-link ap-link--delete" onclick="return confirm('Hapus obat ini?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="ap-empty">
                                Tidak ada data obat.
                            </td>
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
