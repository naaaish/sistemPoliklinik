@extends('layouts.adminpoli')
@section('title', 'Diagnosa')

@section('content')
<div class="diag-page">

  <div class="diag-topbar">
    <div class="diag-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="diag-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="diag-heading">Diagnosa</div>
    </div>

    <button type="button" class="diag-btn-add" onclick="openTambahDiagnosa()">
      <img src="{{ asset('assets/adminPoli/plus1.png') }}" alt="+" class="diag-ic">
      <span>Tambah</span>
    </button>
  </div>

  <div class="diag-card">

    {{-- Search --}}
    <form class="diag-search" method="GET" action="{{ route('adminpoli.diagnosa.index') }}">
      <input type="text" name="q" value="{{ request('q') }}"
             placeholder="Masukkan diagnosa yang dicari" class="diag-search-input">
      <button class="diag-search-btn" type="submit">
        <img src="{{ asset('assets/adminPoli/search.png') }}" alt="cari" class="diag-ic">
        <span>Cari</span>
      </button>
    </form>

    {{-- Upload + Download --}}
    <div class="diag-tools-row">

      <form action="{{ route('adminpoli.diagnosa.import') }}" method="POST" enctype="multipart/form-data" class="diag-upload" id="diagUploadForm">
        @csrf
        <label class="diag-file" for="diagFileInput">
          <input type="file" id="diagFileInput" name="file" accept=".csv,.xlsx,.xls">
          <span id="diagFileLabel">Pilih File</span>
        </label>

        <span class="diag-file-name" id="diagFileName">Belum ada file dipilih</span>

        <button type="submit" class="diag-btn-soft" id="diagUploadBtn" disabled>
          <span>Upload</span>
        </button>

        <small class="diag-file-hint">Max 2MB • Format: CSV / XLSX / XLS</small>
      </form>

      <form action="{{ route('adminpoli.diagnosa.export') }}" method="GET" class="diag-download" id="diagnosaExportForm">
        <input type="date" name="from" value="{{ request('from') }}" class="diag-date" required>
        <span class="diag-sep">s/d</span>
        <input type="date" name="to" value="{{ request('to') }}" class="diag-date" required>

        <select name="format" class="diag-select" required>
          <option value="" disabled selected>Pilih Format</option>
          <option value="csv">CSV</option>
          <option value="excel">Excel</option>
          <option value="pdf">PDF</option>
        </select>

        <button type="submit" name="action" value="preview" class="diag-btn-soft"><span>Preview</span></button>
        <button type="submit" name="action" value="download" class="diag-btn-soft">
          <img src="{{ asset('assets/adminPoli/download.png') }}" alt="download" class="diag-ic">
          <span>Download</span>
        </button>
      </form>

    </div>

    @if(request('from') && request('to'))
      <div class="diag-preview">
        <span>{{ $previewCount ?? 0 }} data diagnosa ditemukan ({{ request('from') }} s/d {{ request('to') }})</span>
      </div>
    @endif

    {{-- Table --}}
    <div class="diag-table">
      <div class="diag-table-head diag-head">
        <div>Diagnosa</div>
        <div>Aksi</div>
      </div>

      <div class="diag-table-body">
        @forelse($diagnosa as $row)
          <div class="diag-row diag-row">
            <div><div class="diag-cell">{{ $row->diagnosa }}</div></div>

            <div class="diag-actions">
              <button type="button" class="diag-act diag-edit js-diag-edit"
                      data-id="{{ $row->id_diagnosa }}"
                      data-text="{{ $row->diagnosa }}">
                <img src="{{ asset('assets/adminPoli/edit.png') }}" class="diag-ic-sm" alt="">
                Edit
              </button>

              <form method="POST" action="{{ route('adminpoli.diagnosa.destroy', $row->id_diagnosa) }}"
                    class="diag-del-form js-diag-delete">
                @csrf
                @method('DELETE')
                <button type="submit" class="diag-act diag-del">
                  <span>Hapus</span>
                  <img src="{{ asset('assets/adminPoli/sampah.png') }}" class="diag-ic-sm" alt="">
                </button>
              </form>
            </div>
          </div>
        @empty
          <div class="diag-row diag-row-empty">
            <div class="diag-empty-span">
              {{ request('q') ? 'Tidak ada diagnosa ditemukan' : 'Belum ada data diagnosa' }}
            </div>
          </div>
        @endforelse
      </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal-overlay" id="modalTambahDiagnosa">
      <div class="modal-card">
        <h3>Tambah Diagnosa</h3>

        <form action="{{ route('adminpoli.diagnosa.store') }}" method="POST">
          @csrf
          <div class="modal-group">
            <label>Diagnosa</label>
            <input type="text" name="diagnosa" required>
          </div>
          <button type="submit" class="modal-btn">Simpan</button>
        </form>
      </div>
    </div>

    {{-- MODAL EDIT --}}
    <div class="modal-overlay" id="modalEditDiagnosa">
      <div class="modal-card">
        <h3>Edit Diagnosa</h3>

        <form method="POST" id="formEditDiagnosa">
          @csrf
          @method('PUT')
          <div class="modal-group">
            <label>Diagnosa</label>
            <input type="text" name="diagnosa" id="editDiagnosaText" required>
          </div>
          <button type="submit" class="modal-btn">Simpan</button>
        </form>
      </div>
    </div>
  </div>

  <div class="diag-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>
</div>
@endsection

@push('scripts')
<script>
  function openTambahDiagnosa(){
    document.getElementById('modalTambahDiagnosa').style.display = 'flex';
  }

  // edit modal
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-diag-edit').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const text = btn.dataset.text ?? '';

        document.getElementById('modalEditDiagnosa').style.display = 'flex';
        document.getElementById('editDiagnosaText').value = text;
        document.getElementById('formEditDiagnosa').action = "{{ url('adminpoli/diagnosa') }}/" + id;
      });
    });

    document.querySelectorAll('form.js-diag-delete').forEach((form) => {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        Swal.fire({
          title: 'Hapus diagnosa ini?',
          text: 'Data diagnosa akan dihapus dari daftar.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus',
          cancelButtonText: 'Batal',
          reverseButtons: true,
        }).then((r) => { if (r.isConfirmed) form.submit(); });
      });
    });

    const input = document.getElementById('diagFileInput');
    const nameEl = document.getElementById('diagFileName');
    const labelEl = document.getElementById('diagFileLabel');
    const btn = document.getElementById('diagUploadBtn');
    const form = document.getElementById('diagUploadForm');

    const MAX_MB = 2, MAX_BYTES = MAX_MB * 1024 * 1024;
    const allowedExt = ['csv','xlsx','xls'];

    function toastError(msg){
      if (window.AdminPoliToast) AdminPoliToast.fire({ icon:'error', title: msg });
      else Swal.fire({ icon:'error', title: msg });
    }

    input?.addEventListener('change', () => {
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
        btn.disabled = true;
        nameEl.textContent = 'Belum ada file dipilih';
        labelEl.textContent = 'Pilih File';
        toastError('Format file harus CSV / XLSX / XLS');
        return;
      }
      if (file.size > MAX_BYTES) {
        input.value = '';
        btn.disabled = true;
        nameEl.textContent = 'Belum ada file dipilih';
        labelEl.textContent = 'Pilih File';
        toastError(`Ukuran file maksimal ${MAX_MB}MB`);
        return;
      }
      nameEl.textContent = file.name;
      labelEl.textContent = 'Ganti File';
      btn.disabled = false;
    });

    form?.addEventListener('submit', (e) => {
      const file = input.files && input.files[0];
      if (!file) { e.preventDefault(); toastError('Pilih file terlebih dahulu sebelum upload.'); }
    });
  });

  // close modal when click overlay
  window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) e.target.style.display = 'none';
  });

// Validasi rentang tanggal export
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('diagnosaExportForm');
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
@endpush