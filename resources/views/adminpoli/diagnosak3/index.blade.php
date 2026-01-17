@extends('layouts.adminpoli')
@section('title','Diagnosa K3')

@section('content')
<div class="obat-page">

  <div class="obat-topbar">
    <div class="obat-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="obat-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="obat-heading">Diagnosa K3</div>
    </div>

    <button type="button" class="obat-btn-add" onclick="openTambahK3()">
      <img src="{{ asset('assets/adminPoli/plus1.png') }}" class="obat-ic" alt="">
      <span>Tambah</span>
    </button>
  </div>

  <div class="obat-card">

    {{-- Search --}}
    <form class="obat-search" method="GET" action="{{ route('adminpoli.diagnosak3.index') }}">
      <input class="obat-search-input" type="text" name="q" value="{{ request('q') }}"
             placeholder="Masukkan nomor / nama penyakit / kategori yang dicari">
      <button class="obat-search-btn" type="submit">
        <img src="{{ asset('assets/adminPoli/search.png') }}" class="obat-ic" alt="">
        <span>Cari</span>
      </button>
    </form>

    {{-- Upload + Download --}}
    <div class="obat-tools-row">
      <form action="{{ route('adminpoli.diagnosak3.import') }}" method="POST" enctype="multipart/form-data" class="obat-upload" id="k3UploadForm">
        @csrf
        <label class="obat-file" for="k3FileInput">
          <input type="file" id="k3FileInput" name="file" accept=".csv,.xlsx,.xls">
          <span id="k3FileLabel">Pilih File</span>
        </label>

        <span class="obat-file-name" id="k3FileName">Belum ada file dipilih</span>

        <button type="submit" class="obat-btn-soft" id="k3UploadBtn" disabled>Upload</button>
        <small class="obat-file-hint">Max 5MB • Format: CSV / XLSX / XLS</small>
      </form>

      <form action="{{ route('adminpoli.diagnosak3.export') }}" method="GET" class="obat-download" id="k3ExportForm">
        <input type="date" name="from" value="{{ request('from') }}" class="obat-date" required>
        <span class="obat-sep">s/d</span>
        <input type="date" name="to" value="{{ request('to') }}" class="obat-date" required>

        <select name="format" class="obat-select" required>
          <option value="" disabled selected>Pilih Format</option>
          <option value="csv">CSV</option>
          <option value="excel">Excel</option>
          <option value="pdf">PDF</option>
        </select>

        <button type="submit" name="action" value="preview" class="obat-btn-soft">Preview</button>
        <button type="submit" name="action" value="download" class="obat-btn-soft">
          <img src="{{ asset('assets/adminPoli/download.png') }}" class="obat-ic" alt="">
          Download
        </button>
      </form>
    </div>

    @if(request('from') && request('to'))
      <div class="obat-preview">
        {{ $previewCount ?? 0 }} data diagnosa K3 ditemukan ({{ request('from') }} s/d {{ request('to') }})
      </div>
    @endif

    {{-- Table --}}
    <div class="obat-table">
      <div class="obat-table-head k3-head">
        <div>Nomor</div>
        <div>Jenis Penyakit</div>
        <div>Kategori</div>
        <div>Aksi</div>
      </div>

      <div class="obat-table-body">
        @forelse($data as $row)
          <div class="obat-row k3-row">
            <div><div class="obat-cell obat-center">{{ $row->id_nb }}</div></div>
            <div><div class="obat-cell">{{ $row->nama_penyakit }}</div></div>
            <div><div class="obat-cell">{{ $row->kategori_penyakit }}</div></div>

            <div class="obat-actions">
              <button type="button" class="obat-act obat-edit js-k3-edit"
                data-id="{{ $row->id_nb }}"
                data-nama="{{ $row->nama_penyakit }}"
                data-kat="{{ $row->kategori_penyakit }}">
                <img src="{{ asset('assets/adminPoli/edit.png') }}" class="obat-ic-sm" alt="">
                Edit
              </button>

              <form method="POST" action="{{ route('adminpoli.diagnosak3.destroy', $row->id_nb) }}" class="obat-del-form js-k3-delete">
                @csrf @method('DELETE')
                <button type="submit" class="obat-act obat-del">
                  <span>Hapus</span>
                  <img src="{{ asset('assets/adminPoli/sampah.png') }}" class="obat-ic-sm" alt="">
                </button>
              </form>
            </div>
          </div>
        @empty
          <div class="obat-row obat-row-empty">
            <div class="obat-empty-span">
              {{ request('q') ? 'Tidak ada diagnosa K3 ditemukan' : 'Belum ada data diagnosa K3' }}
            </div>
          </div>
        @endforelse
      </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal-overlay" id="modalTambahK3">
      <div class="modal-card">
        <h3>Tambah Diagnosa K3</h3>
        <form action="{{ route('adminpoli.diagnosak3.store') }}" method="POST">
          @csrf
          <div class="modal-group">
            <label>Nomor (ID NB)</label>
            <input type="text" name="id_nb" placeholder="contoh: 1.2" required>
          </div>
          <div class="modal-group">
            <label>Jenis Penyakit</label>
            <input type="text" name="nama_penyakit" required>
          </div>
          <div class="modal-group">
            <label>Kategori Penyakit</label>
            <input type="text" name="kategori_penyakit" placeholder="contoh: SALURAN PERNAFASAN" required>
          </div>
          <button type="submit" class="modal-btn">Simpan</button>
        </form>
      </div>
    </div>

    {{-- MODAL EDIT --}}
    <div class="modal-overlay" id="modalEditK3">
      <div class="modal-card">
        <h3>Edit Diagnosa K3</h3>
        <form method="POST" id="formEditK3">
          @csrf @method('PUT')
          <div class="modal-group">
            <label>Nomor (ID NB)</label>
            <input type="text" id="editIdNb" disabled>
          </div>
          <div class="modal-group">
            <label>Jenis Penyakit</label>
            <input type="text" name="nama_penyakit" id="editNamaPenyakit" required>
          </div>
          <div class="modal-group">
            <label>Kategori Penyakit</label>
            <input type="text" name="kategori_penyakit" id="editKategori" required>
          </div>
          <button type="submit" class="modal-btn">Simpan</button>
        </form>
      </div>
    </div>

  </div>

  <div class="obat-foot">Copyright © 2026 ...</div>
</div>
@endsection

@push('scripts')
<script>
  function openTambahK3(){ document.getElementById('modalTambahK3').style.display='flex'; }

  document.addEventListener('DOMContentLoaded', () => {
    // edit modal
    document.querySelectorAll('.js-k3-edit').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('modalEditK3').style.display='flex';
        document.getElementById('editIdNb').value = id;
        document.getElementById('editNamaPenyakit').value = btn.dataset.nama ?? '';
        document.getElementById('editKategori').value = btn.dataset.kat ?? '';
        document.getElementById('formEditK3').action = "{{ url('adminpoli/diagnosa-k3') }}/" + id;
      });
    });

    // delete confirm SweetAlert2
    document.querySelectorAll('form.js-k3-delete').forEach(form => {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        Swal.fire({
          title: 'Hapus diagnosa K3 ini?',
          text: 'Data akan dihapus dari daftar.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus',
          cancelButtonText: 'Batal',
          reverseButtons: true,
        }).then(r => { if (r.isConfirmed) form.submit(); });
      });
    });

    // validasi range tanggal export
    const exportForm = document.getElementById('k3ExportForm');
    const fromEl = exportForm?.querySelector('input[name="from"]');
    const toEl = exportForm?.querySelector('input[name="to"]');

    function toastError(msg){
      if (window.AdminPoliToast) AdminPoliToast.fire({ icon:'error', title: msg });
      else Swal.fire({ icon:'error', title: msg });
    }
    function invalidRange(){
      const f = fromEl?.value, t = toEl?.value;
      if (!f || !t) return false;
      return f > t;
    }
    exportForm?.addEventListener('submit', (e)=>{
      if (invalidRange()){ e.preventDefault(); toastError('Tanggal awal tidak boleh lebih besar dari tanggal akhir.'); }
    });

    // upload: show filename + validate (5MB)
    const input = document.getElementById('k3FileInput');
    const nameEl = document.getElementById('k3FileName');
    const labelEl = document.getElementById('k3FileLabel');
    const btn = document.getElementById('k3UploadBtn');

    const MAX_BYTES = 5 * 1024 * 1024;
    const allowExt = ['csv','xlsx','xls'];

    input?.addEventListener('change', ()=>{
      const file = input.files && input.files[0];
      if(!file){
        nameEl.textContent='Belum ada file dipilih';
        labelEl.textContent='Pilih File';
        btn.disabled=true;
        return;
      }
      const ext = (file.name.split('.').pop()||'').toLowerCase();
      if(!allowExt.includes(ext)){
        input.value='';
        btn.disabled=true;
        nameEl.textContent='Belum ada file dipilih';
        labelEl.textContent='Pilih File';
        toastError('Format file harus CSV / XLSX / XLS');
        return;
      }
      if(file.size > MAX_BYTES){
        input.value='';
        btn.disabled=true;
        nameEl.textContent='Belum ada file dipilih';
        labelEl.textContent='Pilih File';
        toastError('Ukuran file maksimal 5MB');
        return;
      }
      nameEl.textContent=file.name;
      labelEl.textContent='Ganti File';
      btn.disabled=false;
    });
  });

  window.addEventListener('click', (e)=>{
    if(e.target.classList.contains('modal-overlay')) e.target.style.display='none';
  });
</script>
@endpush
