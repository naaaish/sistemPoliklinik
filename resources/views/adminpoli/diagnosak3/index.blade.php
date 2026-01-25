@extends('layouts.adminpoli')

@section('title', 'Diagnosa K3')

@section('content')
<div class="k3-page">

  <div class="k3-topbar">
    <div class="k3-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="k3-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="k3-heading">Diagnosa K3</div>
    </div>

    <div style="display:flex; gap:10px;">
      <button type="button" class="k3-btn-add" onclick="openTambahKategori()">
        <img src="{{ asset('assets/adminPoli/plus1.png') }}" class="k3-ic" alt="+">
        <span>Tambah Kategori</span>
      </button>

      <button type="button" class="k3-btn-add" onclick="openTambahPenyakit(null)">
        <img src="{{ asset('assets/adminPoli/plus1.png') }}" class="k3-ic" alt="+">
        <span>Tambah Penyakit</span>
      </button>
    </div>
  </div>

  <div class="k3-card">

    {{-- Search --}}
    <form class="k3-search" method="GET" action="{{ route('adminpoli.diagnosak3.index') }}">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari id_nb / kategori / penyakit" class="k3-search-input">
      <button class="k3-search-btn" type="submit">
        <img src="{{ asset('assets/adminPoli/search.png') }}" class="k3-ic" alt="cari">
        <span>Cari</span>
      </button>
    </form>

    {{-- Upload + Download --}}
    <div class="k3-tools-row">
      <form action="{{ route('adminpoli.diagnosak3.import') }}" method="POST" enctype="multipart/form-data" class="k3-upload" id="k3UploadForm">
        @csrf
        <label class="k3-file" for="k3FileInput">
          <input type="file" id="k3FileInput" name="file" accept=".csv,.xlsx,.xls">
          <span id="k3FileLabel">Pilih File</span>
        </label>

        <span class="k3-file-name" id="k3FileName">Belum ada file dipilih</span>

        <button type="submit" class="k3-btn-soft" id="k3UploadBtn" disabled>Upload</button>
        <small class="k3-file-hint">Max 5MB • Header wajib: nama_penyakit, kategori_penyakit</small>
      </form>

      <form action="{{ route('adminpoli.diagnosak3.export') }}" method="GET" class="k3-download">
        <input type="date" name="from" value="{{ request('from') }}" class="k3-date" required>
        <span class="k3-sep">s/d</span>
        <input type="date" name="to" value="{{ request('to') }}" class="k3-date" required>

        <select name="format" class="k3-select" required>
          <option value="" disabled selected>Pilih Format</option>
          <option value="csv">CSV</option>
          <option value="excel">Excel</option>
          <option value="pdf">PDF</option>
        </select>

        <button type="submit" name="action" value="preview" class="k3-btn-soft">Preview</button>
        <button type="submit" name="action" value="download" class="k3-btn-soft">
          <img src="{{ asset('assets/adminPoli/download.png') }}" class="k3-ic" alt="download">
          <span>Download</span>
        </button>
      </form>
    </div>

    {{-- LIST KATEGORI + PENYAKIT (sortable) --}}
    <div class="k3-table-body" id="k3CategoryList">

      @forelse($categories as $cat)
        @php
          $kids = $children[$cat->id_nb] ?? collect();
        @endphp

        <div class="k3-cat-row" data-cat-id="{{ $cat->id_nb }}">
          <div class="k3-cat-left">
            <button type="button" class="k3-toggle" onclick="toggleChildren('{{ $cat->id_nb }}')">-</button>
            <div class="k3-cat-title">
              <span style="margin-left:8px;">[{{ $cat->id_nb }}] {{ $cat->nama_penyakit }}</span>
            </div>
          </div>

          <div class="k3-cat-actions">
            <button type="button"
                class="k3-act k3-edit js-edit-kategori"
                data-id="{{ $cat->id_nb }}"
                data-nama="{{ $cat->nama_penyakit }}">
                <img src="{{ asset('assets/adminPoli/edit.png') }}" class="k3-ic-sm" alt=""> Edit
            </button>

            <button type="button" class="k3-act k3-edit"
              onclick="openTambahPenyakit('{{ $cat->id_nb }}')">
              <span>+ Penyakit</span>
            </button>

            <form method="POST" action="{{ route('adminpoli.diagnosak3.kategori.destroy', $cat->id_nb) }}" class="js-k3-delete">
              @csrf
              @method('DELETE')
              <button type="submit" class="k3-act k3-del">
                <span>Hapus</span>
                <img src="{{ asset('assets/adminPoli/sampah.png') }}" class="k3-ic-sm" alt="">
              </button>
            </form>
          </div>
        </div>

        <div class="k3-children" id="children-{{ $cat->id_nb }}" data-parent="{{ $cat->id_nb }}">
          <div class="k3-children-list">
            @forelse($kids as $k)
              <div class="k3-child-row" data-child-id="{{ $k->id_nb }}">
                <div class="k3-id">{{ $k->id_nb }}</div>
                <div class="k3-name">{{ $k->nama_penyakit }}</div>
                <div class="k3-child-actions">
                  <button type="button"
                    class="k3-act k3-edit js-edit-penyakit"
                    data-id="{{ $k->id_nb }}"
                    data-parent="{{ $cat->id_nb }}"
                    data-nama="{{ $k->nama_penyakit }}">

                    <img src="{{ asset('assets/adminPoli/edit.png') }}" class="k3-ic-sm" alt=""> Edit
                  </button>

                  <form method="POST" action="{{ route('adminpoli.diagnosak3.penyakit.destroy', $k->id_nb) }}" class="js-k3-delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="k3-act k3-del">
                      <span>Hapus</span>
                      <img src="{{ asset('assets/adminPoli/sampah.png') }}" class="k3-ic-sm" alt="">
                    </button>
                  </form>
                </div>
              </div>
            @empty
              <div class="k3-child-row" style="opacity:.7;">
                <div class="k3-id">-</div>
                <div class="k3-name">Belum ada penyakit di kategori ini</div>
                <div></div>
              </div>
            @endforelse
          </div>
        </div>

      @empty
        <div class="k3-row k3-row-empty">
          <div class="k3-empty-span">
            {{ request('q') ? 'Tidak ada data ditemukan' : 'Belum ada data Diagnosa K3' }}
          </div>
        </div>
      @endforelse
    </div>

    {{-- MODAL TAMBAH KATEGORI --}}
    <div class="modal-overlay" id="modalTambahKategori">
      <div class="modal-card">
        <h3>Tambah Kategori</h3>
        <form action="{{ route('adminpoli.diagnosak3.kategori.store') }}" method="POST">
          @csrf
          <div class="modal-group">
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" required>
          </div>
          <button type="submit" class="modal-btn">Simpan</button>
        </form>
      </div>
    </div>

    {{-- MODAL EDIT KATEGORI --}}
    <div class="modal-overlay" id="modalEditKategori">
      <div class="modal-card">
        <h3>Edit Kategori</h3>
        <form method="POST" id="formEditKategori">
          @csrf
          @method('PUT')
          <div class="modal-group">
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" id="editNamaKategori" required>
          </div>
          <button type="submit" class="modal-btn">Simpan</button>
        </form>
      </div>
    </div>

    {{-- MODAL TAMBAH PENYAKIT --}}
    <div class="modal-overlay" id="modalTambahPenyakit">
      <div class="modal-card">
        <h3>Tambah Penyakit</h3>
        <form action="{{ route('adminpoli.diagnosak3.penyakit.store') }}" method="POST">
          @csrf

          <div class="modal-group">
            <label>Kategori</label>
            <select name="parent_id" id="tambahParent" class="k3-select" required style="width:100%;">
              <option value="" disabled selected>Pilih Kategori</option>
              @foreach($categories as $c)
                <option value="{{ $c->id_nb }}">[{{ $c->id_nb }}] {{ $c->nama_penyakit }}</option>
              @endforeach
            </select>
          </div>

          <div class="modal-group">
            <label>Nama Penyakit</label>
            <input type="text" name="nama_penyakit" required>
          </div>

          <button type="submit" class="modal-btn">Simpan</button>
        </form>
      </div>
    </div>

    {{-- MODAL EDIT PENYAKIT --}}
    <div class="modal-overlay" id="modalEditPenyakit">
      <div class="modal-card">
        <h3>Edit Penyakit</h3>
        <form method="POST" id="formEditPenyakit">
          @csrf
          @method('PUT')

          <div class="modal-group">
            <label>Kategori</label>
            <select name="parent_id" id="editParent" class="k3-select" required style="width:100%;">
              @foreach($categories as $c)
                <option value="{{ $c->id_nb }}">[{{ $c->id_nb }}] {{ $c->nama_penyakit }}</option>
              @endforeach
            </select>
          </div>

          <div class="modal-group">
            <label>Nama Penyakit</label>
            <input type="text" name="nama_penyakit" id="editNamaPenyakit" required>
          </div>

          <button type="submit" class="modal-btn">Simpan</button>
        </form>
      </div>
    </div>

  </div>

  <div class="k3-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>

</div>
@endsection
@push('scripts')
<script>
function openModal(id){
  var el = document.getElementById(id);
  if (el) el.style.display = 'flex';
}
function closeModal(id){
  var el = document.getElementById(id);
  if (el) el.style.display = 'none';
}

document.addEventListener('click', function(e){
  var overlays = ['modalTambahKategori','modalEditKategori','modalTambahPenyakit','modalEditPenyakit'];
  overlays.forEach(function(id){
    var el = document.getElementById(id);
    if (!el) return;
    if (e.target === el) closeModal(id);
  });
});

function openTambahKategori(){ openModal('modalTambahKategori'); }

function openTambahPenyakit(catId){
  var sel = document.getElementById('tambahParent');
  if (sel && catId) sel.value = catId;
  openModal('modalTambahPenyakit');
}

/**
 * === Template route update (ANTI mismatch route) ===
 * Route kamu: /diagnosak3/kategori/{id_nb} dan /diagnosak3/penyakit/{id_nb}
 * Jadi action harus bener-bener sesuai.
 *
 * Kita bikin URL dummy pakai id_nb = '__ID__' lalu replace.
 */
var KATEGORI_UPDATE_TPL = "{{ route('adminpoli.diagnosak3.kategori.update', ['id_nb' => '__ID__']) }}";
var PENYAKIT_UPDATE_TPL = "{{ route('adminpoli.diagnosak3.penyakit.update', ['id_nb' => '__ID__']) }}";

function setFormAction(formId, tpl, id){
  var form = document.getElementById(formId);
  if (!form) return;
  // encode karena id bisa ada titik (contoh 1.2)
  form.action = tpl.replace('__ID__', encodeURIComponent(id));
}

function openEditKategori(id, nama){
  var input = document.getElementById('editNamaKategori');
  if (input) input.value = nama || '';
  setFormAction('formEditKategori', KATEGORI_UPDATE_TPL, id);
  openModal('modalEditKategori');
}

function openEditPenyakit(id, parentId, nama){
  var parentEl = document.getElementById('editParent');
  var namaEl = document.getElementById('editNamaPenyakit');
  if (parentEl) parentEl.value = parentId || '';
  if (namaEl) namaEl.value = nama || '';
  setFormAction('formEditPenyakit', PENYAKIT_UPDATE_TPL, id);
  openModal('modalEditPenyakit');
}

function toggleChildren(catId){
  var box = document.getElementById('children-'+catId);
  var btn = null;

  if (box && box.previousElementSibling){
    btn = box.previousElementSibling.querySelector('.k3-toggle');
  }
  if (!box) return;

  var hidden = (box.style.display === 'none');
  box.style.display = hidden ? 'block' : 'none';
  if (btn) btn.textContent = hidden ? '-' : '+';
}

document.addEventListener('DOMContentLoaded', function(){

  // ===== Bind tombol edit (kategori & penyakit) =====
  document.querySelectorAll('.js-edit-kategori').forEach(function(btn){
    btn.addEventListener('click', function(){
      openEditKategori(btn.dataset.id, btn.dataset.nama || '');
    });
  });

  document.querySelectorAll('.js-edit-penyakit').forEach(function(btn){
    btn.addEventListener('click', function(){
      openEditPenyakit(btn.dataset.id, btn.dataset.parent, btn.dataset.nama || '');
    });
  });

  // ===== Upload helper =====
  var input = document.getElementById('k3FileInput');
  var nameEl = document.getElementById('k3FileName');
  var labelEl = document.getElementById('k3FileLabel');
  var btnUp = document.getElementById('k3UploadBtn');

  var MAX_BYTES = 5 * 1024 * 1024;
  var allowedExt = ['csv','xlsx','xls'];

  if (input){
    input.addEventListener('change', function(){
      var file = input.files && input.files[0];
      if (!file){
        if (nameEl) nameEl.textContent = 'Belum ada file dipilih';
        if (labelEl) labelEl.textContent = 'Pilih File';
        if (btnUp) btnUp.disabled = true;
        return;
      }

      var ext = (file.name.split('.').pop() || '').toLowerCase();
      if (allowedExt.indexOf(ext) === -1){
        Swal.fire({icon:'error', title:'Format file harus CSV / XLSX / XLS'});
        input.value = '';
        if (btnUp) btnUp.disabled = true;
        return;
      }

      if (file.size > MAX_BYTES){
        Swal.fire({icon:'error', title:'Ukuran file melebihi 5MB'});
        input.value = '';
        if (btnUp) btnUp.disabled = true;
        return;
      }

      if (labelEl) labelEl.textContent = 'File Dipilih';
      if (nameEl) nameEl.textContent = file.name;
      if (btnUp) btnUp.disabled = false;
    });
    document.querySelectorAll('.js-k3-confirm-submit').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      Swal.fire({
        icon: 'question',
        title: 'Simpan data ini?',
        showCancelButton: true,
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal'
      }).then(function(r){
        if (r.isConfirmed) form.submit();
      });
    });
  });
}

  // ===== SweetAlert confirm delete =====
  document.querySelectorAll('.js-k3-delete').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Yakin mau hapus?',
        text: 'Data yang dihapus tidak bisa dikembalikan.',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
      }).then(function(r){
        if (r.isConfirmed) form.submit();
      });
    });
  });
});

document.addEventListener('DOMContentLoaded', function(){
  function toast(icon, msg){
    if (!msg) return;

    // pakai toast samping seperti Obat
    if (window.AdminPoliToast) {
      AdminPoliToast.fire({ icon: icon, title: msg });
      return;
    }

    // fallback
    Swal.fire({ icon: icon, title: msg, timer: 1600, showConfirmButton: false });
  }

  


});
document.addEventListener('DOMContentLoaded', function () {
  function toast(icon, msg) {
    if (!msg) return;

    if (window.AdminPoliToast) {
      AdminPoliToast.fire({ icon: icon, title: msg });
    } else {
      Swal.fire({
        icon: icon,
        title: msg,
        timer: 1600,
        showConfirmButton: false
      });
    }
  }

  const flash = window.__FLASH__ || {};

  toast('success', flash.success);
  toast('error', flash.error);
  toast('error', flash.validation);
});



</script>

@endpush
