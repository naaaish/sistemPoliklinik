@extends('layouts.adminpoli')

@section('title', 'Obat')

@section('content')
<div class="obat-page">

    {{-- Header: Back + Title + Tambah --}}
    <div class="obat-topbar">
        <div class="obat-left">
            <a href="{{ route('adminpoli.dashboard') }}" class="obat-back-img" title="Kembali">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
            </a>
            <div class="obat-heading">Obat</div>
        </div>
        
        <button type="button" class="obat-btn-add" onclick="openTambah()">
            <img src="{{ asset('assets/adminPoli/plus1.png') }}" alt="+" class="obat-ic">
            <span>Tambah</span>
        </button>
    </div>

    <div class="obat-card">

        {{-- Row 1: Search --}}
        <form class="obat-search" method="GET" action="{{ route('adminpoli.obat.index') }}">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Masukkan nama obat yang dicari"
                class="obat-search-input"
            >
            <button class="obat-search-btn" type="submit">
                <img src="{{ asset('assets/adminPoli/search.png') }}" alt="cari" class="obat-ic">
                <span>Cari</span>
            </button>
        </form>

        {{-- Row 2: Upload + Download (sesuai desain: baris kecil rapi) --}}
        <div class="obat-tools-row">

            <form action="{{ route('adminpoli.obat.import') }}"
                method="POST"
                enctype="multipart/form-data"
                class="obat-upload"
                id="obatUploadForm">
            @csrf

            <label class="obat-file" for="obatFileInput">
                <input type="file" id="obatFileInput" name="file" accept=".csv,.xlsx,.xls">
                <span id="obatFileLabel">Pilih File</span>
            </label>

            <span class="obat-file-name" id="obatFileName">Belum ada file dipilih</span>

            <button type="submit" class="obat-btn-soft" id="obatUploadBtn" disabled>
                <span>Upload</span>
            </button>

            <small class="obat-file-hint">
                Max 5MB • Format: CSV / XLSX / XLS
            </small>
            </form>


            {{-- Download + rentang tanggal --}}
            <form action="{{ route('adminpoli.obat.export') }}" method="GET" class="obat-download" id="obatExportForm">
                <input type="date" name="from" value="{{ request('from') }}" class="obat-date" required>
                <span class="obat-sep">s/d</span>
                <input type="date" name="to" value="{{ request('to') }}" class="obat-date" required>

                <select name="format" class="obat-select" required>
                    <option value="" disabled selected>Pilih Format</option>
                    <option value="csv">CSV</option>
                    <option value="excel">Excel</option>
                    <option value="pdf">PDF</option>
                </select>

                {{-- Preview --}}
                <button type="submit" name="action" value="preview" class="obat-btn-soft">
                    <span>Preview</span>
                </button>

                {{-- Download --}}
                <button type="submit" name="action" value="download" class="obat-btn-soft">
                    <img src="{{ asset('assets/adminPoli/download.png') }}" alt="download" class="obat-ic">
                    <span>Download</span>
                </button>
            </form>

        </div>

        @if(request('from') && request('to'))
            <div class="obat-preview">
                <span>
                    {{ $previewCount ?? 0 }} data obat ditemukan
                    ({{ request('from') }} s/d {{ request('to') }})
                </span>
            </div>
        @endif

        {{-- Table --}}
        <div class="obat-table">
            <div class="obat-table-head">
                <div>Nama</div>
                <div>Harga Satuan</div>
                <div>Aksi</div>
            </div>

            <div class="obat-table-body">
                @forelse($obat as $row)
                    @php
                        $pk = $row->id_obat ?? $row->kode_obat ?? null;
                        $nama = $row->nama_obat ?? $row->nama ?? '-';
                        $harga = $row->harga ?? null;
                    @endphp

                    <div class="obat-row">
                        <div><div class="obat-cell">{{ $nama }}</div></div>
                        <div><div class="obat-cell obat-center">{{ $harga ? 'Rp'.number_format($harga,0,',','.') : '-' }}</div></div>

                        <div class="obat-actions">
                            <button
                                type="button"
                                class="obat-act obat-edit js-edit"
                                data-id="{{ $pk }}"
                                data-nama="{{ $nama }}"
                                data-harga="{{ $harga }}"
                            >
                                <img src="{{ asset('assets/adminPoli/edit.png') }}" class="obat-ic-sm" alt="">
                                Edit
                            </button>

                            <form method="POST" action="{{ route('adminpoli.obat.destroy', $pk) }}" class="obat-del-form js-obat-delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="obat-act obat-del">
                                    <span>Hapus</span>
                                    <img src="{{ asset('assets/adminPoli/sampah.png') }}" alt="hapus" class="obat-ic-sm">
                                </button>
                            </form>

                        </div>
                    </div>
                @empty
                <div class="obat-row obat-row-empty">
                    <div class="obat-empty-span">
                        {{ request('q') ? 'Tidak ada obat ditemukan' : 'Belum ada data obat' }}
                    </div>
                </div>
                @endforelse

            </div>
        </div>

        {{-- ================= MODAL TAMBAH OBAT ================= --}}
        <div class="modal-overlay" id="modalTambah">
            <div class="modal-card">
                <h3>Tambah Data Obat</h3>

                <form action="{{ route('adminpoli.obat.store') }}" method="POST">
                    @csrf

                    <div class="modal-group">
                        <label>Nama Obat</label>
                        <input type="text" name="nama_obat" required>
                    </div>

                    <div class="modal-group">
                        <label>Harga Satuan</label>
                        <input type="number" name="harga" min="1" step="1" required>
                    </div>

                    <button type="submit" class="modal-btn">Simpan</button>
                </form>
            </div>
        </div>

        {{-- ================= MODAL EDIT OBAT ================= --}}
        <div class="modal-overlay" id="modalEdit">
            <div class="modal-card">
                <h3>Edit Data Obat</h3>

                <form method="POST" id="formEdit">
                    @csrf
                    @method('PUT')

                    <div class="modal-group">
                        <label>Nama Obat</label>
                        <input type="text" name="nama_obat" id="editNama" required>
                    </div>

                    <div class="modal-group">
                        <label>Harga Satuan</label>
                        <input type="number" name="harga" id="editHarga" required>
                    </div>

                    <button type="submit" class="modal-btn">Simpan</button>
                </form>
            </div>
        </div>
        <div class="obat-table-foot">
            <div class="obat-total">
                Total
                @if($obat instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $obat->total() }}
                @else
                    {{ $obat->count() }}
                @endif
            </div>

            <form method="GET" action="{{ route('adminpoli.obat.index') }}" class="obat-lines">
                {{-- keep query biar ga reset --}}
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                <span class="obat-lines-label">Lines per page</span>

                <select name="per_page" class="obat-lines-select" onchange="this.form.submit()">
                    <option value="10"  {{ request('per_page','10')=='10' ? 'selected' : '' }}>10</option>
                    <option value="25"  {{ request('per_page')=='25' ? 'selected' : '' }}>25</option>
                    <option value="50"  {{ request('per_page')=='50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page')=='100' ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page')=='all' ? 'selected' : '' }}>All</option>
                </select>
            </form>
        </div>
    </div>

    <div class="obat-foot">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>

</div>
@endsection

@push('scripts')
<script>
// Upload file
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('obatFileInput');
  const nameEl = document.getElementById('obatFileName');
  const labelEl = document.getElementById('obatFileLabel');
  const btn = document.getElementById('obatUploadBtn');
  const form = document.getElementById('obatUploadForm');

  const MAX_MB = 5;
  const MAX_BYTES = MAX_MB * 1024 * 1024;
  const allowedExt = ['csv','xlsx','xls'];

  function toastError(msg){
    if (window.AdminPoliToast) {
      AdminPoliToast.fire({ icon:'error', title: msg });
    } else {
      Swal.fire({ icon:'error', title: msg });
    }
  }

  if (!input) return;

  input.addEventListener('change', () => {
    const file = input.files && input.files[0];
    if (!file){
      nameEl.textContent = 'Belum ada file dipilih';
      labelEl.textContent = 'Pilih File';
      btn.disabled = true;
      return;
    }

    const ext = (file.name.split('.').pop() || '').toLowerCase();

    // validasi ekstensi
    if (!allowedExt.includes(ext)) {
      input.value = '';
      nameEl.textContent = 'Belum ada file dipilih';
      labelEl.textContent = 'Pilih File';
      btn.disabled = true;
      toastError('Format file harus CSV / XLSX / XLS');
      return;
    }

    // validasi ukuran
    if (file.size > MAX_BYTES) {
      input.value = '';
      nameEl.textContent = 'Belum ada file dipilih';
      labelEl.textContent = 'Pilih File';
      btn.disabled = true;
      toastError(`Ukuran file maksimal ${MAX_MB}MB`);
      return;
    }

    // tampilkan nama file
    nameEl.textContent = file.name;
    labelEl.textContent = 'Ganti File';
    btn.disabled = false;
  });

  // guard sebelum submit (kalau user klik Upload tanpa file)
  form?.addEventListener('submit', (e) => {
    const file = input.files && input.files[0];
    if (!file){
      e.preventDefault();
      toastError('Pilih file terlebih dahulu sebelum upload.');
    }
  });
});

// Hapus dengan konfirmasi
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form.js-obat-delete').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      Swal.fire({
        title: 'Hapus data obat ini?',
        text: 'Obat akan dihapus dari daftar',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
});

// Edit isi data
function toDateInputValue(v){
  if(!v) return '';
  // ambil YYYY-MM-DD saja
  if (typeof v === 'string' && v.length >= 10) {
    return v.substring(0, 10);
  }
  return v;
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.js-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const id    = btn.dataset.id;
      const nama  = btn.dataset.nama;
      const harga = btn.dataset.harga;

      document.getElementById('modalEdit').style.display = 'flex';
      document.getElementById('editNama').value  = nama ?? '';
      document.getElementById('editHarga').value = harga ?? '';

      document.getElementById('formEdit').action =
        "{{ url('adminpoli/obat') }}/" + id;
    });
  });
});
</script>
@endpush

<script>
    function openTambah(){
        document.getElementById('modalTambah').style.display = 'flex';
    }

    window.onclick = function(e){
        if(e.target.classList.contains('modal-overlay')){
            e.target.style.display = 'none';
        }
    }

// Validasi rentang tanggal export
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('obatExportForm');
  if (!form) return;

  const fromEl = form.querySelector('input[name="from"]');
  const toEl   = form.querySelector('input[name="to"]');

  function toastError(msg){
    if (window.AdminPoliToast) AdminPoliToast.fire({ icon:'error', title: msg });
    else Swal.fire({ icon:'error', title: msg });
  }

  function isInvalidRange(){
    const from = fromEl?.value;
    const to = toEl?.value;
    if (!from || !to) return false; // required sudah handle
    return from > to; // aman karena format YYYY-MM-DD
  }

  // validasi saat submit (preview atau download)
  form.addEventListener('submit', (e) => {
    if (isInvalidRange()) {
      e.preventDefault();
      toastError('Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
    }
  });

  // optional: auto alert saat user ganti tanggal
  [fromEl, toEl].forEach(el => {
    el?.addEventListener('change', () => {
      if (isInvalidRange()) toastError('Rentang tanggal tidak valid. Perbaiki tanggalnya.');
    });
  });
});
</script>
