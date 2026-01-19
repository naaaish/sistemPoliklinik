@extends('layouts.adminpoli')

@section('title', 'Dokter/Pemeriksa')

@section('content')
<div class="dp-page">
    <div class="dp-topbar">
        <div class="dp-left">
        <a href="{{ route('adminpoli.dashboard') }}" class="dp-back-img" title="Kembali">
            <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
        </a>
        <div class="dp-heading">Dokter/Pemeriksa</div>
        </div>

        <button type="button" class="dp-btn-add" id="dpOpenTambah">
            <img src="{{ asset('assets/adminPoli/plus1.png') }}" class="dp-ic" alt="+">
            <span>Tambah Dokter/Pemeriksa</span>
        </button>
    </div>

    <div class="dp-card">

    {{-- Search --}}
    <form class="dp-search" method="GET" action="{{ route('adminpoli.dokter_pemeriksa.index') }}">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari dokter/pemeriksa" class="dp-search-input">
      <button class="dp-search-btn" type="submit">
        <img src="{{ asset('assets/adminPoli/search.png') }}" class="dp-ic" alt="cari">
        <span>Cari</span>
      </button>
    </form>

    {{-- Table --}}
    <div class="dp-table-head">
      <div>Nama</div>
      <div>Jenis</div>
      <div>Status</div>
      <div>Jadwal</div>
      <div>Aksi</div>
    </div>

    <div class="dp-table-body">
      @forelse($rows as $d)
        <div class="dp-row"
            data-tipe="{{ $d->tipe }}"
            data-id="{{ $d->id }}"
            data-nama="{{ e($d->nama) }}"
            data-jenis="{{ e($d->jenis) }}"
            data-status="{{ e($d->status) }}">

          <div class="dp-cell">
            <span class="dp-celltext">{{ $d->nama }}</span>
          </div>

          <div class="dp-cell dp-center">
            <span class="dp-plain">{{ $d->jenis }}</span>
          </div>

          <div class="dp-status-wrap">
            <select class="dp-status-select" data-tipe="{{ $d->tipe }}" data-id="{{ $d->id }}">
                <option value="Aktif" {{ ($d->status ?? 'Aktif') == 'Aktif' ? 'selected' : '' }}>
                Aktif
                </option>
                <option value="Nonaktif" {{ ($d->status ?? 'Aktif') == 'Nonaktif' ? 'selected' : '' }}>
                Nonaktif
                </option>
            </select>
            <img
                src="{{ asset('assets/adminPoli/dropdown.png') }}"
                class="dp-status-arrow"
                alt="dropdown"
            >
          </div>

          <div class="dp-cell dp-center dp-cell-jadwal">
            <button type="button" class="dp-jadwal-btn" data-tipe="{{ $d->tipe }}" data-id="{{ $d->id }}">
                <span class="dp-jadwal-text">Lihat</span>
                <span class="dp-jadwal-icons">
                <img src="{{ asset('assets/adminPoli/eye.png') }}" class="dp-ic-sm" alt="lihat">
                </span>
            </button>
          </div>


          <div class="dp-cell dp-center">
            <div class="dp-actions">
              <button type="button" class="dp-act dp-edit dp-edit-btn" data-tipe="{{ $d->tipe }}" data-id="{{ $d->id }}">
                <img src="{{ asset('assets/adminPoli/edit.png') }}" class="dp-ic-sm" alt="edit">
                <span>Edit</span>
              </button>


              <form class="dp-del-form" method="POST"
                    action="{{ $d->tipe === 'dokter'
                        ? route('adminpoli.dokter_pemeriksa.dokter.destroy', $d->id)
                        : route('adminpoli.dokter_pemeriksa.pemeriksa.destroy', $d->id)
                    }}"
                    onsubmit="return confirm('Hapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="dp-act dp-del">
                    <span>Hapus</span>
                    <img src="{{ asset('assets/adminPoli/sampah.png') }}" class="dp-ic-sm" alt="hapus">
                </button>
              </form>
            </div>
          </div>

        </div>
      @empty
        <div class="dp-row dp-row-empty">
          <div class="dp-empty-span">Tidak ada data</div>
        </div>
      @endforelse
    </div>

  </div>

  <div class="dp-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>

  {{-- =========================
      MODAL: TAMBAH
  ========================= --}}
  <div class="dp-modal" id="dpModalTambah" aria-hidden="true">
    <div class="dp-modal-backdrop" data-close="dpModalTambah"></div>
    <div class="dp-modal-card">
      <div class="dp-modal-title">Tambah Dokter/Pemeriksa</div>

      <form method="POST" action="{{ route('adminpoli.dokter_pemeriksa.dokter.store') }}" id="dpFormTambah">
        @csrf

        <div class="dp-form-row">
          <label class="dp-label">ID</label>
          <div class="dp-colon">:</div>
          <input class="dp-input" name="id_dokter" type="text" maxlength="20" required placeholder="ID Dokter">
        </div>

        <div class="dp-form-row">
          <label class="dp-label">Nama</label>
          <div class="dp-colon">:</div>
          <input class="dp-input" name="nama" type="text" required placeholder="Nama Dokter/Pemeriksa">
        </div>

        <div class="dp-form-row">
          <label class="dp-label">Jenis Dokter/Pemeriksa</label>
          <div class="dp-colon">:</div>

          {{-- jika kamu pakai tabel pemeriksa --}}
          <select class="dp-input dp-select2" name="jenis" required>
            <option value="" disabled selected>Pilih Jenis Dokter/Pemeriksa</option>
            @foreach($jenisList as $j)
              <option value="{{ $j->nama_pemeriksa }}">{{ $j->nama_pemeriksa }}</option>
            @endforeach
          </select>

          {{-- kalau tidak pakai pemeriksa, ganti jadi input text:
          <input class="dp-input" name="jenis" type="text" required>
          --}}
        </div>

        <div class="dp-form-row">
          <label class="dp-label">Status</label>
          <div class="dp-colon">:</div>
          <select class="dp-input dp-select2" name="status">
            <option value="Aktif" selected>Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
          </select>
        </div>

        <div class="dp-form-row dp-form-row-top">
          <label class="dp-label">Jadwal</label>
          <div class="dp-colon">:</div>

          <div class="dp-jadwal-wrap">
            <div id="dpTambahJadwalList" class="dp-jadwal-list">
              {{-- baris jadwal akan di-generate via JS --}}
            </div>

            <button type="button" class="dp-btn-soft" id="dpTambahJadwalBtn">
              <span class="dp-plus2">+</span> Tambah Jadwal
            </button>
          </div>
        </div>

        <div class="dp-actions-bottom">
          <button type="submit" class="dp-submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  {{-- =========================
      MODAL: EDIT
  ========================= --}}
  <div class="dp-modal" id="dpModalEdit" aria-hidden="true">
    <div class="dp-modal-backdrop" data-close="dpModalEdit"></div>
    <div class="dp-modal-card">
      <div class="dp-modal-title">Edit Dokter/Pemeriksa</div>

      <form method="POST" action="#" id="dpFormEdit">
        @csrf
        @method('PUT')

        <div class="dp-form-row">
          <label class="dp-label">Nama</label>
          <div class="dp-colon">:</div>
          <input class="dp-input" name="nama" id="dpEditNama" type="text" required>
        </div>

        <div class="dp-form-row">
          <label class="dp-label">Jenis Dokter/Pemeriksa</label>
          <div class="dp-colon">:</div>
          <select class="dp-input dp-select2" name="jenis" id="dpEditJenis" required>
            @foreach($jenisList as $j)
              <option value="{{ $j->nama_pemeriksa }}">{{ $j->nama_pemeriksa }}</option>
            @endforeach
          </select>
        </div>

        <div class="dp-form-row">
          <label class="dp-label">Status</label>
          <div class="dp-colon">:</div>
          <select class="dp-input dp-select2" name="status" id="dpEditStatus">
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
          </select>
        </div>

        <div class="dp-form-row dp-form-row-top">
          <label class="dp-label">Jadwal</label>
          <div class="dp-colon">:</div>

          <div class="dp-jadwal-wrap">
            <div id="dpEditJadwalList" class="dp-jadwal-list"></div>

            <button type="button" class="dp-btn-soft" id="dpEditTambahJadwalBtn">
              <span class="dp-plus2">+</span> Tambah Jadwal
            </button>
          </div>
        </div>

        <div class="dp-actions-bottom">
          <button type="submit" class="dp-submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  {{-- =========================
      MODAL: LIHAT JADWAL
  ========================= --}}
  <div class="dp-modal" id="dpModalJadwal" aria-hidden="true">
    <div class="dp-modal-backdrop" data-close="dpModalJadwal"></div>
    <div class="dp-modal-card dp-modal-card-small">
      <div class="dp-modal-title">Jadwal Dokter/Pemeriksa</div>

      <div class="dp-jadwal-view" id="dpJadwalViewBox">
        {{-- isi via JS --}}
      </div>

      <div class="dp-actions-bottom">
        <button type="button" class="dp-submit" data-close-btn="dpModalJadwal">Simpan</button>
      </div>
    </div>
  </div>

</div>

{{-- =========================
    JS Modal + Jadwal dynamic
========================= --}}
<script>
(function(){
  const openModal = (id) => {
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.add('is-open');
    el.setAttribute('aria-hidden', 'false');
  };

  const closeModal = (id) => {
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.remove('is-open');
    el.setAttribute('aria-hidden', 'true');
  };

  // close backdrop
  document.querySelectorAll('.dp-modal-backdrop').forEach(b => {
    b.addEventListener('click', () => closeModal(b.dataset.close));
  });
  // close button (jadwal modal)
  document.querySelectorAll('[data-close-btn]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.closeBtn));
  });

  // =========================
  // Row template jadwal
  // =========================
  const jadwalRow = (idx, data = {}) => {
    const hari = data.hari || '';
    const jm = data.jam_mulai || '';
    const js = data.jam_selesai || '';

    return `
      <div class="dp-jrow">
        <input class="dp-jinput" name="jadwal[${idx}][hari]" type="text" placeholder="Hari" value="${hari}">
        <input class="dp-jinput" name="jadwal[${idx}][jam_mulai]" type="time" value="${jm}">
        <span class="dp-jsep">-</span>
        <input class="dp-jinput" name="jadwal[${idx}][jam_selesai]" type="time" value="${js}">
        <button type="button" class="dp-jremove" title="Hapus jadwal">✕</button>
      </div>
    `;
  };

  const bindRemoveButtons = (wrap) => {
    wrap.querySelectorAll('.dp-jremove').forEach(btn => {
      btn.onclick = () => btn.closest('.dp-jrow')?.remove();
    });
  };

  // =========================
  // Tambah Modal
  // =========================
  const btnOpenTambah = document.getElementById('dpOpenTambah');
  const tambahList = document.getElementById('dpTambahJadwalList');
  const tambahJadwalBtn = document.getElementById('dpTambahJadwalBtn');

  let tambahIdx = 0;
  const addTambahRow = () => {
    tambahList.insertAdjacentHTML('beforeend', jadwalRow(tambahIdx++));
    bindRemoveButtons(tambahList);
  };

  btnOpenTambah?.addEventListener('click', () => {
    // reset jadwal list biar bersih saat buka
    tambahList.innerHTML = '';
    tambahIdx = 0;
    addTambahRow(); // default 1 baris seperti desain
    openModal('dpModalTambah');
  });

  tambahJadwalBtn?.addEventListener('click', addTambahRow);

  // =========================
  // Edit Modal
  // =========================
  const editNama = document.getElementById('dpEditNama');
  const editJenis = document.getElementById('dpEditJenis');
  const editStatus = document.getElementById('dpEditStatus');
  const editForm = document.getElementById('dpFormEdit');
  const editList = document.getElementById('dpEditJadwalList');
  const editTambahJadwalBtn = document.getElementById('dpEditTambahJadwalBtn');

  let editIdx = 0;
  const addEditRow = (data={}) => {
    editList.insertAdjacentHTML('beforeend', jadwalRow(editIdx++, data));
    bindRemoveButtons(editList);
  };

  document.querySelectorAll('.dp-edit-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const row = btn.closest('.dp-row');
      const id = row.dataset.id;

      editNama.value = row.dataset.nama || '';
      editJenis.value = row.dataset.jenis || '';
      editStatus.value = row.dataset.status || 'Aktif';

      editForm.action = `{{ url('admin/dokter-pemeriksa') }}/${id}`;

      // load jadwal untuk edit
      editList.innerHTML = '';
      editIdx = 0;

      try{
        const res = await fetch(`{{ url('admin/dokter-pemeriksa') }}/${id}/jadwal`);
        const json = await res.json();

        if(json.jadwal && json.jadwal.length){
          json.jadwal.forEach(j => addEditRow(j));
        }else{
          addEditRow();
        }
      }catch(e){
        addEditRow();
      }

      openModal('dpModalEdit');
    });
  });

  editTambahJadwalBtn?.addEventListener('click', () => addEditRow());

  // =========================
  // Jadwal Modal (lihat)
  // =========================
  const jadwalBox = document.getElementById('dpJadwalViewBox');

  document.querySelectorAll('.dp-jadwal-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const tipe = btn.dataset.tipe;
      const id   = btn.dataset.id;
      jadwalBox.innerHTML = `<div class="dp-jadwal-line">Memuat...</div>`;

      try{
        const res = await fetch(`{{ url('admin/dokter-pemeriksa') }}/${tipe}/${id}/jadwal`);
        const json = await res.json();

        if(json.jadwal && json.jadwal.length){
          jadwalBox.innerHTML = json.jadwal.map(j =>
            `<div class="dp-jadwal-line">${j.hari}, ${j.jam_mulai}-${j.jam_selesai}</div>`
          ).join('');
        }else{
          jadwalBox.innerHTML = `<div class="dp-jadwal-line">Belum ada jadwal</div>`;
        }
      }catch(e){
        jadwalBox.innerHTML = `<div class="dp-jadwal-line">Gagal memuat jadwal</div>`;
      }

      openModal('dpModalJadwal');
    });
  });

})();


(function(){
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  document.querySelectorAll('.dp-status-select').forEach(sel => {
    sel.addEventListener('change', async () => {
      const tipe = sel.dataset.tipe;
      const id   = sel.dataset.id;
      const val  = sel.value;

      const url = (tipe === 'pemeriksa')
        ? `/adminpoli/dokter-pemeriksa/pemeriksa/${id}/status`
        : `/adminpoli/dokter-pemeriksa/dokter/${id}/status`;

      try{
        const res = await fetch(url, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(token ? {'X-CSRF-TOKEN': token} : {})
          },
          body: JSON.stringify({ status: val })
        });

        if(!res.ok) throw new Error('HTTP ' + res.status);

        if(typeof showDpAlert === 'function') showDpAlert('Status berhasil diperbarui.');
      }catch(e){
        if(typeof showDpAlert === 'function') showDpAlert('Gagal update status.', true);
      }
    });
  });
})();

(function(){
  // --- helpers ---
  function openModal(modalId){
    const m = document.getElementById(modalId);
    if(m) m.classList.add('is-open');
  }

  function esc(s){
    return String(s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[c]));
  }

  // --- config (sesuaikan kalau id modal kamu beda) ---
  const MODAL_ID = 'dpModalJadwal';
  const VIEW_ID  = 'dpJadwalView';

  const view = document.getElementById(VIEW_ID);

  // --- bind click ke semua tombol lihat jadwal ---
  document.querySelectorAll('.dp-jadwal-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const tipe = btn.dataset.tipe;  // 'dokter' / 'pemeriksa'
      const id   = btn.dataset.id;

      // buka modal dulu
      openModal(MODAL_ID);

      if(view) view.innerHTML = '<div class="dp-jadwal-line">Memuat...</div>';

      try{
        // endpoint sesuai route kamu: /adminpoli/dokter-pemeriksa/{tipe}/{id}/jadwal
        const url = `/adminpoli/dokter-pemeriksa/${encodeURIComponent(tipe)}/${encodeURIComponent(id)}/jadwal`;

        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if(!res.ok) throw new Error('HTTP ' + res.status);

        const data = await res.json();
        const jadwal = data && data.jadwal ? data.jadwal : [];

        if(!Array.isArray(jadwal)) throw new Error('bad jadwal');

        if(view){
          if(jadwal.length === 0){
            view.innerHTML = '<div class="dp-jadwal-line">Tidak ada jadwal.</div>';
          }else{
            view.innerHTML = jadwal.map(j => {
              const hari = esc(j.hari ?? '-');
              const jm   = esc(j.jam_mulai ?? '--:--');
              const js   = esc(j.jam_selesai ?? '--:--');
              return `<div class="dp-jadwal-line">${hari} : ${jm} - ${js}</div>`;
            }).join('');
          }
        }
      }catch(e){
        if(view) view.innerHTML = '<div class="dp-jadwal-line">Gagal mengambil data.</div>';
      }
    });
  });

  // Optional: close modal via data-close
  document.querySelectorAll('[data-close]').forEach(el => {
    el.addEventListener('click', () => {
      const id = el.getAttribute('data-close');
      const m = document.getElementById(id);
      if(m) m.classList.remove('is-open');
    });
  });
})();

</script>
@endsection
