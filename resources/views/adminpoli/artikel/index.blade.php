@extends('layouts.adminpoli')

@section('title', 'Artikel')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/adminPoli/artikel.css') }}">
@endpush

@section('content')
<div class="artikel-page">

  <div class="artikel-topbar">
    <div class="artikel-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="artikel-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="artikel-heading">Artikel</div>
    </div>

    <a href="{{ route('adminpoli.artikel.create') }}" class="artikel-btn-add">
      <img src="{{ asset('assets/adminPoli/plus1.png') }}" alt="+" class="artikel-ic">
      <span>Tambah</span>
    </a>
  </div>

  <div class="artikel-card">

    {{-- SEARCH (placeholder mengikuti screenshot) --}}
    <form class="artikel-search" method="GET" action="{{ route('adminpoli.artikel.index') }}">
      <input
        type="text"
        name="q"
        value="{{ request('q') }}"
        placeholder="Masukkan judul artikel yang dicari"
        class="artikel-search-input"
      >
      <button class="artikel-search-btn" type="submit">
        <img src="{{ asset('assets/adminPoli/search.png') }}" alt="cari" class="artikel-ic">
        <span>Cari</span>
      </button>
    </form>

    {{-- UPLOAD PDF/WORD (opsional, tetap di index) --}}
    <div class="artikel-tools-row">
      <form action="{{ route('adminpoli.artikel.import') }}"
            method="POST"
            enctype="multipart/form-data"
            class="artikel-upload"
            id="artikelUploadForm">
        @csrf

        <label class="artikel-file" for="artikelFileInput">
          <input type="file" id="artikelFileInput" name="file" accept=".pdf,.doc,.docx">
          <span id="artikelFileLabel">Pilih File</span>
        </label>

        <span class="artikel-file-name" id="artikelFileName">Belum ada file dipilih</span>

        <button type="submit" class="artikel-btn-soft" id="artikelUploadBtn" disabled>
          <span>Upload</span>
        </button>
        <small class="artikel-file-hint">Max 10MB • Format: PDF / DOC / DOCX</small>
      </form>
    </div>

    {{-- TABLE LIST (Judul + Aksi) --}}
    <div class="artikel-table">
      <div class="artikel-table-head artikel-two-col">
        <div>Judul</div>
        <div>Aksi</div>
      </div>

      <div class="artikel-table-body">
        @forelse($artikel as $row)
          @php
            $pk = $row->id_artikel;
            $judul = $row->judul_artikel ?? '-';
          @endphp

          <div class="artikel-row artikel-two-col">
            <div>
              <div class="artikel-cell artikel-title-cell">
                {{ $judul }}
              </div>
            </div>

            <div>
              <div class="artikel-actions">
                <a href="{{ route('adminpoli.artikel.edit', $pk) }}" class="artikel-act artikel-edit">
                  Edit
                  <img src="{{ asset('assets/adminPoli/edit.png') }}" class="artikel-ic-sm" alt="">
                </a>

                <form method="POST"
                      action="{{ route('adminpoli.artikel.destroy', $pk) }}"
                      class="artikel-del-form js-artikel-delete">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="artikel-act artikel-del">
                    Hapus
                    <img src="{{ asset('assets/adminPoli/sampah.png') }}" alt="hapus" class="artikel-ic-sm">
                  </button>
                </form>
              </div>
            </div>
          </div>
        @empty
          <div class="artikel-row artikel-row-empty">
            <div class="artikel-empty-span">
              {{ request('q') ? 'Tidak ada artikel ditemukan' : 'Belum ada data artikel' }}
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <div class="artikel-table-foot">
            <div class="artikel-total">
                Total
                @if($artikel instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $artikel->total() }}
                @else
                    {{ $artikel->count() }}
                @endif
            </div>

            <form method="GET" action="{{ route('adminpoli.artikel.index') }}" class="artikel-lines">
                {{-- keep query biar ga reset --}}
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                <span class="artikel-lines-label">Lines per page</span>

                <select name="per_page" class="artikel-lines-select" onchange="this.form.submit()">
                    <option value="10"  {{ request('per_page','10')=='10' ? 'selected' : '' }}>10</option>
                    <option value="25"  {{ request('per_page')=='25' ? 'selected' : '' }}>25</option>
                    <option value="50"  {{ request('per_page')=='50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page')=='100' ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page')=='all' ? 'selected' : '' }}>All</option>
                </select>
            </form>
        </div>

  </div>

  <div class="artikel-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // upload enable/disable
  const input = document.getElementById('artikelFileInput');
  const nameEl = document.getElementById('artikelFileName');
  const labelEl = document.getElementById('artikelFileLabel');
  const btn = document.getElementById('artikelUploadBtn');

  if (input) {
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) {
        nameEl.textContent = 'Belum ada file dipilih';
        labelEl.textContent = 'Pilih File';
        btn.disabled = true;
        return;
      }
      nameEl.textContent = file.name;
      labelEl.textContent = 'Ganti File';
      btn.disabled = false;
    });
  }

  // konfirmasi hapus
  document.querySelectorAll('form.js-artikel-delete').forEach((f) => {
    f.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!window.Swal) return f.submit();

      Swal.fire({
        title: 'Hapus artikel ini?',
        text: 'Artikel akan dihapus dari daftar',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) f.submit();
      });
    });
  });
});
</script>
@endpush
