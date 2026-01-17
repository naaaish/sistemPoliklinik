@extends('layouts.adminpoli')

@section('title', 'Diagnosa K3')

@section('content')
<div class="obat-page">

  <div class="obat-topbar">
    <div class="obat-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="obat-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="obat-heading">Diagnosa K3</div>
    </div>

    <div style="display:flex; gap:10px;">
      <button type="button" class="obat-btn-add" onclick="openTambahKategori()">
        <img src="{{ asset('assets/adminPoli/plus1.png') }}" class="obat-ic" alt="+">
        <span>Tambah Kategori</span>
      </button>

      <button type="button" class="obat-btn-add" onclick="openTambahPenyakit(null)">
        <img src="{{ asset('assets/adminPoli/plus1.png') }}" class="obat-ic" alt="+">
        <span>Tambah Penyakit</span>
      </button>
    </div>
  </div>

  <div class="obat-card">

    {{-- Search --}}
    <form class="obat-search" method="GET" action="{{ route('adminpoli.diagnosak3.index') }}">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari id_nb / kategori / penyakit" class="obat-search-input">
      <button class="obat-search-btn" type="submit">
        <img src="{{ asset('assets/adminPoli/search.png') }}" class="obat-ic" alt="cari">
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
        <small class="obat-file-hint">Max 5MB • Header wajib: nama_penyakit, kategori_penyakit</small>
      </form>

      <form action="{{ route('adminpoli.diagnosak3.export') }}" method="GET" class="obat-download">
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
          <img src="{{ asset('assets/adminPoli/download.png') }}" class="obat-ic" alt="download">
          <span>Download</span>
        </button>
      </form>
    </div>

    {{-- LIST KATEGORI + PENYAKIT (sortable) --}}
    <div class="obat-table-body" id="k3CategoryList">

      @forelse($categories as $cat)
        @php
          $kids = $children[$cat->id_nb] ?? collect();
        @endphp

        <div class="k3-cat-row" data-cat-id="{{ $cat->id_nb }}">
          <div class="k3-cat-left">
            <button type="button" class="k3-toggle" onclick="toggleChildren('{{ $cat->id_nb }}')">-</button>
            <div class="k3-cat-title">
              <b class="k3-handle" style="cursor:grab;">☰</b>
              <span style="margin-left:8px;">[{{ $cat->id_nb }}] {{ $cat->nama_penyakit }}</span>
            </div>
          </div>

          <div class="k3-cat-actions">
            <button type="button"
                class="obat-act obat-edit js-edit-kategori"
                data-id="{{ $cat->id_nb }}"
                data-nama="{{ $cat->nama_penyakit }}">
                <img src="{{ asset('assets/adminPoli/edit.png') }}" class="obat-ic-sm" alt=""> Edit
            </button>

            <button type="button" class="obat-act obat-edit"
              onclick="openTambahPenyakit('{{ $cat->id_nb }}')">
              <span>+ Penyakit</span>
            </button>

            <form method="POST" action="{{ route('adminpoli.diagnosak3.kategori.destroy', $cat->id_nb) }}" class="js-k3-delete">
              @csrf
              @method('DELETE')
              <button type="submit" class="obat-act obat-del">
                <span>Hapus</span>
                <img src="{{ asset('assets/adminPoli/sampah.png') }}" class="obat-ic-sm" alt="">
              </button>
            </form>
          </div>
        </div>

        <div class="k3-children" id="children-{{ $cat->id_nb }}" data-parent="{{ $cat->id_nb }}">
          <div class="k3-children-list">
            @forelse($kids as $k)
              <div class="k3-child-row" data-child-id="{{ $k->id_nb }}">
                <div class="k3-id"><b class="k3-child-handle" style="cursor:grab;">☰</b> {{ $k->id_nb }}</div>
                <div class="k3-name">{{ $k->nama_penyakit }}</div>
                <div class="k3-child-actions">
                  <button type="button"
                    class="obat-act obat-edit js-edit-penyakit"
                    data-id="{{ $k->id_nb }}"
                    data-parent="{{ $cat->id_nb }}"
                    data-nama="{{ $k->nama_penyakit }}">

                    <img src="{{ asset('assets/adminPoli/edit.png') }}" class="obat-ic-sm" alt=""> Edit
                  </button>

                  <form method="POST" action="{{ route('adminpoli.diagnosak3.penyakit.destroy', $k->id_nb) }}" class="js-k3-delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="obat-act obat-del">
                      <span>Hapus</span>
                      <img src="{{ asset('assets/adminPoli/sampah.png') }}" class="obat-ic-sm" alt="">
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
        <div class="obat-row obat-row-empty">
          <div class="obat-empty-span">
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
            <select name="parent_id" id="tambahParent" class="obat-select" required style="width:100%;">
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
            <select name="parent_id" id="editParent" class="obat-select" required style="width:100%;">
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

  <div class="obat-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }

document.addEventListener('click', (e)=>{
  const overlays = ['modalTambahKategori','modalEditKategori','modalTambahPenyakit','modalEditPenyakit'];
  overlays.forEach(id=>{
    const el = document.getElementById(id);
    if (!el) return;
    if (e.target === el) closeModal(id);
  });
});

function openTambahKategori(){ openModal('modalTambahKategori'); }

// function openEditKategori(id, nama){
//   document.getElementById('editNamaKategori').value = nama;
//   document.getElementById('formEditKategori').action =
//     "{{ url('adminpoli/diagnosak3/kategori') }}/" + encodeURIComponent(id);
//   openModal('modalEditKategori');
// }

function openTambahPenyakit(catId){
  const sel = document.getElementById('tambahParent');
  if (catId) sel.value = catId;
  openModal('modalTambahPenyakit');
}

// function openEditPenyakit(id, parentId, nama){
//   document.getElementById('editParent').value = parentId;
//   document.getElementById('editNamaPenyakit').value = nama;
//   document.getElementById('formEditPenyakit').action =
//     "{{ url('adminpoli/diagnosak3/penyakit') }}/" + encodeURIComponent(id);
//   openModal('modalEditPenyakit');
// }

function toggleChildren(catId){
  const box = document.getElementById('children-'+catId);
  const btn = box && box.previousElementSibling
    ? box.previousElementSibling.querySelector('.k3-toggle')
    : null;

  if (!box) return;

  const hidden = box.style.display === 'none';
  box.style.display = hidden ? 'block' : 'none';
  if (btn) btn.textContent = hidden ? '-' : '+';
}

// ===== isi modal EDIT KATEGORI =====
document.querySelectorAll('.js-edit-kategori').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.dataset.id;
    const nama = btn.dataset.nama || '';

    document.getElementById('editNamaKategori').value = nama;
    document.getElementById('formEditKategori').action =
      "{{ url('adminpoli/diagnosak3/kategori') }}/" + encodeURIComponent(id);

    openModal('modalEditKategori');
  });
});

// ===== isi modal EDIT PENYAKIT =====
document.querySelectorAll('.js-edit-penyakit').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.dataset.id;
    const parent = btn.dataset.parent;
    const nama = btn.dataset.nama || '';

    document.getElementById('editParent').value = parent;
    document.getElementById('editNamaPenyakit').value = nama;
    document.getElementById('formEditPenyakit').action =
      "{{ url('adminpoli/diagnosak3/penyakit') }}/" + encodeURIComponent(id);

    openModal('modalEditPenyakit');
  });
});


// ===== REORDER (tanpa JSON) =====
async function saveOrder() {
  const categories = Array.from(document.querySelectorAll('.k3-cat-row'))
    .map(el => el.getAttribute('data-cat-id'))
    .filter(Boolean);

  const childrenMap = {};
  document.querySelectorAll('.k3-children').forEach(box => {
    const parent = box.getAttribute('data-parent');
    const ids = Array.from(box.querySelectorAll('.k3-child-row'))
      .map(el => el.getAttribute('data-child-id'))
      .filter(Boolean);
    childrenMap[parent] = ids;
  });

  const fd = new FormData();
  categories.forEach(id => fd.append('categories[]', id));

  Object.keys(childrenMap).forEach(parentId => {
    childrenMap[parentId].forEach(childId => {
      fd.append('children[' + parentId + '][]', childId);
    });
  });

  try {
    const res = await fetch("{{ route('adminpoli.diagnosak3.reorder') }}", {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}",
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: fd
    });

    if (!res.ok) throw new Error('Gagal simpan urutan');

    Swal.fire({
      icon:'success',
      title:'Urutan berhasil disimpan',
      timer: 1200,
      showConfirmButton:false
    }).then(()=> location.reload());

  } catch (err) {
    Swal.fire({ icon:'error', title:'Urutan gagal disimpan', text: err.message || '' });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // ===== Upload helper =====
  const input = document.getElementById('k3FileInput');
  const nameEl = document.getElementById('k3FileName');
  const labelEl = document.getElementById('k3FileLabel');
  const btn = document.getElementById('k3UploadBtn');

  const MAX_BYTES = 5 * 1024 * 1024;
  const allowedExt = ['csv','xlsx','xls'];

  if (input){
    input.addEventListener('change', ()=>{
      const file = input.files && input.files[0];
      if (!file){
        nameEl.textContent = 'Belum ada file dipilih';
        labelEl.textContent = 'Pilih File';
        btn.disabled = true;
        return;
      }
      const ext = (file.name.split('.').pop() || '').toLowerCase();
      if (!allowedExt.includes(ext)){
        Swal.fire({icon:'error', title:'Format file harus CSV / XLSX / XLS'});
        input.value = '';
        btn.disabled = true;
        return;
      }
      if (file.size > MAX_BYTES){
        Swal.fire({icon:'error', title:'Ukuran file melebihi 5MB'});
        input.value = '';
        btn.disabled = true;
        return;
      }
      labelEl.textContent = 'File Dipilih';
      nameEl.textContent = file.name;
      btn.disabled = false;
    });
  }

  // ===== SweetAlert confirm delete =====
  document.querySelectorAll('.js-k3-delete').forEach(form=>{
    form.addEventListener('submit', (e)=>{
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Yakin mau hapus?',
        text: 'Data yang dihapus tidak bisa dikembalikan.',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
      }).then((r)=>{
        if (r.isConfirmed) form.submit();
      });
    });
  });

  // ===== Sortable kategori =====
  const catList = document.getElementById('k3CategoryList');
  if (catList){
    new Sortable(catList, {
      handle: '.k3-handle',
      animation: 150,
      draggable: '.k3-cat-row',
      onEnd: saveOrder
    });
  }

  // ===== Sortable penyakit per kategori =====
  document.querySelectorAll('.k3-children').forEach(box=>{
    const list = box.querySelector('.k3-children-list');
    if (!list) return;
    new Sortable(list, {
      handle: '.k3-child-handle',
      animation: 150,
      draggable: '.k3-child-row',
      onEnd: saveOrder
    });
  });
});
</script>

@if(session('success'))
<script>
Swal.fire({
  icon: 'success',
  title: "{{ addslashes(session('success')) }}",
  timer: 1600,
  showConfirmButton: false
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
  icon: 'error',
  title: "{{ addslashes(session('error')) }}"
});
</script>
@endif

@if($errors->any())
<script>
  Swal.fire({
    icon: 'error',
    title: "{{ addslashes($errors->first()) }}"
  });
</script>
@endif

@endpush
