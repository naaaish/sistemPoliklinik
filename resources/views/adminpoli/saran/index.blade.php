@extends('layouts.adminpoli')

@section('title', 'Saran')

@section('content')
<div class="saran-page">
    {{-- Header: Back + Title + Tambah --}}
    <div class="saran-topbar">
        <div class="saran-left">
            <a href="{{ route('adminpoli.dashboard') }}" class="saran-back-img" title="Kembali">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
            </a>
            <div class="saran-heading">Saran</div>
        </div>

        <button type="button" class="saran-btn-add" onclick="openTambah()">
            <img src="{{ asset('assets/adminPoli/plus1.png') }}" alt="+" class="saran-ic">
            <span>Tambah</span>
        </button>
    </div>

    <div class="saran-card">

        {{-- Row 1: Search --}}
        <form class="saran-search" method="GET" action="{{ route('adminpoli.saran.index') }}">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Masukkan saran / diagnosa yang dicari"
                class="saran-search-input"
            >
            <button class="saran-search-btn" type="submit">
                <img src="{{ asset('assets/adminPoli/search.png') }}" alt="cari" class="saran-ic">
                <span>Cari</span>
            </button>
        </form>

        {{-- Row 2: Upload + Download --}}
        <div class="saran-tools-row">

            <form action="{{ route('adminpoli.saran.import') }}"
                method="POST"
                enctype="multipart/form-data"
                class="saran-upload"
                id="saranUploadForm">
            @csrf

            <label class="saran-file" for="saranFileInput">
                <input type="file" id="saranFileInput" name="file" accept=".csv,.xlsx,.xls">
                <span id="saranFileLabel">Pilih File</span>
            </label>

            <span class="saran-file-name" id="saranFileName">Belum ada file dipilih</span>

            <button type="submit" class="saran-btn-soft" id="saranUploadBtn" disabled>
                <span>Upload</span>
            </button>

            <small class="saran-file-hint">
                Max 5MB • Format: CSV / XLSX / XLS
            </small>
            </form>

            {{-- Download + rentang tanggal --}}
            <form action="{{ route('adminpoli.saran.export') }}" method="GET" class="saran-download" id="saranExportForm">
                <input type="date" name="from" value="{{ request('from') }}" class="saran-date" required>
                <span class="saran-sep">s/d</span>
                <input type="date" name="to" value="{{ request('to') }}" class="saran-date" required>

                <select name="format" class="saran-select" required>
                    <option value="" disabled selected>Pilih Format</option>
                    <option value="csv">CSV</option>
                    <option value="excel">Excel</option>
                    <option value="pdf">PDF</option>
                </select>

                {{-- Preview --}}
                <button type="submit" name="action" value="preview" class="saran-btn-soft">
                    <span>Preview</span>
                </button>

                {{-- Download --}}
                <button type="submit" name="action" value="download" class="saran-btn-soft {{ $previewCount === 0 ? 'saran-disabled' : '' }}">
                    <span>Download</span>
                </button>
            </form>
        </div>
        {{-- TABLE --}}
        <div class="saran-table-head">
            <div>Saran</div>
            <div>Diagnosa</div>
            <div>Aksi</div>
        </div>

        <div class="saran-table-body">
        @forelse($saran as $row)
            @php
                $pk = $row->id_saran;
                $text = $row->saran_text ?? '-';
                $idDiag = $row->id_diagnosa ?? '';
                $diagText = $row->diagnosa_text ?? ($idDiag ?: '-');
            @endphp

            <div class="saran-row">
                <div><div class="saran-cell">{{ $text }}</div></div>
                <div><div class="saran-cell saran-center">{{ $diagText }}</div></div>

                <div class="saran-actions">
                    <button
                        type="button"
                        class="saran-act saran-edit js-edit"
                        data-id="{{ $pk }}"
                        data-saran="{{ e($text) }}"
                        data-iddiagnosa="{{ $idDiag }}"
                    >
                        <img src="{{ asset('assets/adminPoli/edit.png') }}" class="saran-ic-sm" alt="">
                        Edit
                    </button>

                    <form method="POST" action="{{ route('adminpoli.saran.destroy', $pk) }}" class="saran-del-form js-saran-delete">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="saran-act saran-del">
                            <span>Hapus</span>
                            <img src="{{ asset('assets/adminPoli/sampah.png') }}" alt="hapus" class="saran-ic-sm">
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="saran-row saran-row-empty">
                <div class="saran-empty-span">
                    {{ request('q') ? 'Tidak ada saran ditemukan' : 'Belum ada data saran' }}
                </div>
            </div>
        @endforelse
        </div>


        {{-- ================= MODAL TAMBAH SARAN ================= --}}
        <div class="modal-overlay" id="modalTambah">
            <div class="modal-card">
                <h3>Tambah Data Saran</h3>

                <form action="{{ route('adminpoli.saran.store') }}" method="POST">
                    @csrf

                    <div class="modal-group">
                        <label>Diagnosa</label>
                        <select name="id_diagnosa" class="modal-select js-diagnosa-select" required>
                            <option value="" disabled selected>Pilih Diagnosa</option>
                            @foreach($diagnosaList as $d)
                                <option value="{{ $d->id_diagnosa }}">{{ $d->diagnosa }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-group">
                        <label>Saran</label>
                        <textarea name="saran" class="modal-textarea" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="modal-btn">Simpan</button>
                </form>
            </div>
        </div>

        {{-- ================= MODAL EDIT SARAN ================= --}}
        <div class="modal-overlay" id="modalEdit">
            <div class="modal-card">
                <h3>Edit Data Saran</h3>

                <form method="POST" id="formEdit">
                    @csrf
                    @method('PUT')

                    <div class="modal-group">
                        <label>Diagnosa</label>
                        <select name="id_diagnosa" class="modal-select js-diagnosa-select" id="editDiagnosa" required>

                            <option value="" disabled>Pilih Diagnosa</option>
                            @foreach($diagnosaList as $d)
                                <option value="{{ $d->id_diagnosa }}">{{ $d->diagnosa }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-group">
                        <label>Saran</label>
                        <textarea name="saran" class="modal-textarea" id="editSaran" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="modal-btn">Simpan</button>
                </form>
            </div>
        </div>

        <div class="saran-table-foot">
            <div class="saran-total">
                Total
                @if($saran instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $saran->total() }}
                @else
                    {{ $saran->count() }}
                @endif
            </div>

            <form method="GET" action="{{ route('adminpoli.saran.index') }}" class="saran-lines">
                {{-- keep query biar ga reset --}}
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                <span class="saran-lines-label">Lines per page</span>

                <select name="per_page" class="saran-lines-select" onchange="this.form.submit()">
                    <option value="10"  {{ request('per_page','10')=='10' ? 'selected' : '' }}>10</option>
                    <option value="25"  {{ request('per_page')=='25' ? 'selected' : '' }}>25</option>
                    <option value="50"  {{ request('per_page')=='50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page')=='100' ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page')=='all' ? 'selected' : '' }}>All</option>
                </select>
            </form>
        </div>
    </div>

    <div class="saran-foot">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  function initSelect2(modalId){
    if (typeof $ === 'undefined' || !$.fn.select2) return;

    const $modal = $(modalId);
    const $select = $modal.find('.js-diagnosa-select');

    // prevent double init
    $select.each(function(){
      if ($(this).hasClass("select2-hidden-accessible")) return;

      $(this).select2({
        dropdownParent: $modal,
        width: '100%',
        placeholder: 'Pilih Diagnosa',
        allowClear: true
      });
    });
  }

  // init untuk kedua modal (supaya search langsung aktif)
  initSelect2('#modalTambah');
  initSelect2('#modalEdit');

  // kalau modal dibuka setelah init, tetap aman.
});

// Upload file (copy behavior obat)
document.addEventListener('DOMContentLoaded', () => {
  const input  = document.getElementById('saranFileInput');
  const nameEl = document.getElementById('saranFileName');
  const labelEl= document.getElementById('saranFileLabel');
  const btn    = document.getElementById('saranUploadBtn');
  const form   = document.getElementById('saranUploadForm');

  const MAX_MB = 5;
  const MAX_BYTES = MAX_MB * 1024 * 1024;
  const allowedExt = ['csv','xlsx','xls'];

  function toastError(msg){
    if (window.AdminPoliToast) AdminPoliToast.fire({ icon:'error', title: msg });
    else Swal.fire({ icon:'error', title: msg });
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

    if (!allowedExt.includes(ext)) {
      input.value = '';
      nameEl.textContent = 'Belum ada file dipilih';
      labelEl.textContent = 'Pilih File';
      btn.disabled = true;
      toastError('Format file harus CSV / XLSX / XLS');
      return;
    }

    if (file.size > MAX_BYTES) {
      input.value = '';
      nameEl.textContent = 'Belum ada file dipilih';
      labelEl.textContent = 'Pilih File';
      btn.disabled = true;
      toastError(`Ukuran file maksimal ${MAX_MB}MB`);
      return;
    }

    nameEl.textContent = file.name;
    labelEl.textContent = 'Ganti File';
    btn.disabled = false;
  });

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
  document.querySelectorAll('form.js-saran-delete').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      Swal.fire({
        title: 'Hapus data saran ini?',
        text: 'Saran akan dihapus dari daftar',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) form.submit();
      });
    });
  });
});

// Edit isi data
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.js-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const saran = btn.dataset.saran || '';
      const idDiagnosa = btn.dataset.iddiagnosa || '';

      document.getElementById('modalEdit').style.display = 'flex';
      document.getElementById('editSaran').value = saran;
      document.getElementById('editDiagnosa').value = idDiagnosa;

      document.getElementById('formEdit').action =
        "{{ url('adminpoli/saran') }}/" + id;
    });
  });
});

function openTambah(){
  document.getElementById('modalTambah').style.display = 'flex';
}

window.onclick = function(e){
  if(e.target.classList.contains('modal-overlay')){
    e.target.style.display = 'none';
  }
}

// Validasi rentang tanggal export (copy obat)
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('saranExportForm');
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
    if (!from || !to) return false;
    return from > to;
  }

  form.addEventListener('submit', (e) => {
    if (isInvalidRange()) {
      e.preventDefault();
      toastError('Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
    }
  });

  [fromEl, toEl].forEach(el => {
    el?.addEventListener('change', () => {
      if (isInvalidRange()) toastError('Rentang tanggal tidak valid. Perbaiki tanggalnya.');
    });
  });
});
</script>
@endpush
