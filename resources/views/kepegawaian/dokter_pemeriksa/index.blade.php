@extends('layouts.kepegawaian')

@section('title', 'Dokter/Pemeriksa')

@section('content')
<div class="dp-page">
  <div class="dp-topbar">
    <div class="dp-left">
      <a href="{{ route('kepegawaian.dashboard') }}" class="dp-back-img" title="Kembali">
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
    <form class="dp-search" method="GET" action="{{ route('kepegawaian.dokter_pemeriksa.index') }}">
      <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="Cari dokter/pemeriksa" class="dp-search-input">
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
        @php
          $jadwalStr = '';

          if ($d->tipe === 'dokter') {
            $list = $d->jadwalDokter ?? $d->jadwal_dokter ?? [];
            $parts = [];

            if (is_iterable($list)) {
              foreach ($list as $j) {
                $hari = $j->hari ?? '';
                $mulai = isset($j->jam_mulai) ? substr($j->jam_mulai,0,5) : '';
                $selesai = isset($j->jam_selesai) ? substr($j->jam_selesai,0,5) : '';
                if ($hari && $mulai && $selesai) $parts[] = $hari.'|'.$mulai.'|'.$selesai;
              }
            }

            $jadwalStr = implode(';;', $parts);
          } else {
            $jadwalStr = 'Senin|07:00|16:00;;Selasa|07:00|16:00;;Rabu|07:00|16:00;;Kamis|07:00|16:00;;Jumat|07:00|16:00';
          }
        @endphp

        <div class="dp-row"
          data-tipe="{{ $d->tipe }}"
          data-id="{{ $d->id }}"
          data-nama="{{ e($d->nama) }}"
          data-jenis="{{ e($d->jenis) }}"
          data-status="{{ e($d->status) }}"
          data-jadwal="{{ e($d->jadwalStr ?? '') }}"
        >
          <div class="dp-cell">
            <span class="dp-celltext">{{ $d->nama }}</span>
          </div>

          <div class="dp-cell dp-center">
            <span class="dp-plain">{{ $d->jenis }}</span>
          </div>

          <div class="dp-cell dp-center">
            <div class="dp-status-wrap">
              <form method="POST" action="{{ $d->tipe === 'dokter' ? route('kepegawaian.dokter_pemeriksa.dokter.status', $d->id) : route('kepegawaian.dokter_pemeriksa.pemeriksa.status', $d->id) }}">
                @csrf
                @method('PATCH')

                <select name="status" class="dp-status-select" onchange="this.form.submit()">
                  <option value="Aktif" {{ $d->status === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                  <option value="Nonaktif" {{ $d->status === 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
              </form>
              <img src="{{ asset('assets/adminPoli/dropdown.png') }}" class="dp-status-arrow" alt="v">
            </div>
          </div>

          <div class="dp-cell dp-center dp-cell-jadwal">
            <div class="dp-jadwal-wrap">
              <button type="button"
                class="dp-jadwal-btn"
                data-tipe="{{ $d->tipe }}"
                data-jadwal="{{ $d->jadwalStr ?? '' }}">
                <span class="dp-jadwal-text">Lihat</span>
                <span class="dp-jadwal-icons">
                  <img src="{{ asset('assets/adminPoli/eye.png') }}" class="dp-ic-sm" alt="lihat">
                </span>
              </button>
            </div>
          </div>

          <div class="dp-actions">
            <button type="button"
              class="dp-act dp-edit dp-edit-btn"
              data-tipe="{{ $d->tipe }}"
              data-id="{{ $d->id }}"
              data-nama="{{ e($d->nama) }}"
              data-jenis="{{ e($d->jenis) }}"
              data-status="{{ e($d->status) }}"
            >
              <img src="{{ asset('assets/adminPoli/edit.png') }}" class="dp-ic-sm" alt="">
              Edit
            </button>

            <form method="POST"
              class="dp-del-form js-dp-delete"
              action="{{ $d->tipe === 'dokter'
                ? route('kepegawaian.dokter_pemeriksa.dokter.destroy', $d->id)
                : route('kepegawaian.dokter_pemeriksa.pemeriksa.destroy', $d->id)
              }}"
            >
              @csrf
              @method('DELETE')
              <button type="submit" class="dp-act dp-del">
                <span>Hapus</span>
                <img src="{{ asset('assets/adminPoli/sampah.png') }}" class="dp-ic-sm" alt="">
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="dp-row dp-row-empty">
          <div class="dp-empty-span">
            {{ request('q') ? 'Tidak ada dokter/pemeriksa ditemukan' : 'Belum ada data dokter/pemeriksa' }}
          </div>
        </div>
      @endforelse
    </div>

    <div class="dp-foot">
      Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>
  </div>
</div>

  {{-- FLASH (buat toast) --}}


  {{-- ================= MODAL TAMBAH ================= --}}
  <div class="modal-overlay" id="dpModalTambah">
    <div class="modal-card dp-modal-wide">
      <h3>Tambah Dokter/Pemeriksa</h3>

      <form method="POST" id="dpFormTambah" action="{{ route('kepegawaian.dokter_pemeriksa.dokter.store') }}">
        @csrf

        <div class="modal-group">
          <label>Tipe</label>
          <select name="tipe" id="dpTambahTipe" class="dp-modal-select" required>
            <option value="dokter">Dokter</option>
            <option value="pemeriksa">Pemeriksa</option>
          </select>
        </div>

        {{-- DOKTER --}}
        <div class="dp-tambah-dokter">
          <input type="hidden" name="id_dokter" id="dpTambahIdDokter">
          <div class="modal-group">
            <label>Nama Dokter</label>
            <input type="text" name="nama" id="dpTambahNamaDokter" required>
          </div>

          <div class="modal-group">
            <label>Jenis Dokter</label>
            <input type="text" name="jenis_dokter" id="dpTambahJenisDokter" placeholder="Contoh: Dokter Umum" required>
          </div>

          <div class="modal-group">
            <label>Status</label>
            <select name="status" id="dpTambahStatusDokter" class="dp-modal-select" required>
              <option value="Aktif">Aktif</option>
              <option value="Nonaktif">Nonaktif</option>
            </select>
          </div>

          <div class="modal-group">
            <label>Jadwal (boleh lebih dari satu)</label>
            <div id="dpTambahJadwalList" class="dp-jadwal-list"></div>
            <button type="button" class="dp-btn-soft dp-btn-soft-full" id="dpTambahJadwalBtn">
              <span class="dp-plus2">+</span> Tambah Jadwal
            </button>
          </div>
        </div>

        {{-- PEMERIKSA --}}
        <div class="dp-tambah-pemeriksa" style="display:none;">
          <input type="hidden" name="id_pemeriksa" id="dpTambahIdPemeriksa">
          <div class="modal-group">
            <label>Nama Pemeriksa</label>
            <input type="text" name="nama_pemeriksa" id="dpTambahNamaPemeriksa" required>
          </div>

          <div class="modal-group">
            <label>Status</label>
            <select name="status" id="dpTambahStatusPemeriksa" class="dp-modal-select" required>
              <option value="Aktif">Aktif</option>
              <option value="Nonaktif">Nonaktif</option>
            </select>
          </div>

          <div class="modal-group">
            <label>Jadwal</label>
            <div class="dp-jadwal-fixed">Senin - Jumat, 07:00 - 16:00</div>
          </div>
        </div>

        <button type="submit" class="modal-btn">Simpan</button>
      </form>
    </div>
  </div>

{{-- ================= MODAL EDIT ================= --}}
<div class="modal-overlay" id="dpModalEdit">
  <div class="modal-card dp-modal-wide">
    <h3>Edit Dokter/Pemeriksa</h3>

    <form method="POST" id="dpFormEdit">
      @csrf
      @method('PUT')

      <input type="hidden" id="dpEditTipe" value="dokter">

      {{-- DOKTER --}}
      <div class="dp-edit-dokter">
        <div class="modal-group">
          <label>Nama Dokter</label>
          <input type="text" name="nama" id="dpEditNamaDokter" required>
        </div>

        <div class="modal-group">
          <label>Jenis Dokter</label>
          <input type="text" name="jenis_dokter" id="dpEditJenisDokter" required>
        </div>

        <div class="modal-group">
          <label>Status</label>
          <select name="status" id="dpEditStatusDokter" class="dp-modal-select" required>
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
          </select>
        </div>

        <div class="modal-group">
          <label>Jadwal</label>
          <div id="dpEditJadwalList" class="dp-jadwal-list"></div>
          <button type="button" class="dp-btn-soft dp-btn-soft-full" id="dpEditJadwalBtn">
            <span class="dp-plus2">+</span> Tambah Jadwal
          </button>
        </div>
      </div>

      {{-- PEMERIKSA --}}
      <div class="dp-edit-pemeriksa" style="display:none;">
        <div class="modal-group">
          <label>Nama Pemeriksa</label>
          <input type="text" name="nama_pemeriksa" id="dpEditNamaPemeriksa" required>
        </div>

        <div class="modal-group">
          <label>Status</label>
          <select name="status" id="dpEditStatusPemeriksa" class="dp-modal-select" required>
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
          </select>
        </div>

        <div class="modal-group">
          <label>Jadwal</label>
          <div class="dp-jadwal-fixed">Senin - Jumat, 07:00 - 16:00</div>
        </div>
      </div>

      <button type="submit" class="modal-btn">Simpan</button>
    </form>
  </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const dokterStoreUrl = "{{ route('kepegawaian.dokter_pemeriksa.dokter.store') }}";
  const pemeriksaStoreUrl = "{{ route('kepegawaian.dokter_pemeriksa.pemeriksa.store') }}";
  const dokterUpdateBase = "{{ url('kepegawaian/dokter-pemeriksa/dokter') }}";
  const pemeriksaUpdateBase = "{{ url('kepegawaian/dokter-pemeriksa/pemeriksa') }}";

  const genId = (prefix) => prefix + String(Date.now());

  const btnOpenTambah = document.getElementById('dpOpenTambah');
  const modalTambah = document.getElementById('dpModalTambah');
  const modalEdit = document.getElementById('dpModalEdit');

  const formTambah = document.getElementById('dpFormTambah');
  const tambahTipe = document.getElementById('dpTambahTipe');

  const tambahDokterBox = document.querySelector('.dp-tambah-dokter');
  const tambahPemeriksaBox = document.querySelector('.dp-tambah-pemeriksa');

  const tambahJadwalList = document.getElementById('dpTambahJadwalList');
  const tambahJadwalBtn = document.getElementById('dpTambahJadwalBtn');

  const editDokterBox = document.querySelector('.dp-edit-dokter');
  const editPemeriksaBox = document.querySelector('.dp-edit-pemeriksa');
  const formEdit = document.getElementById('dpFormEdit');
  const editTipe = document.getElementById('dpEditTipe');
  const editJadwalList = document.getElementById('dpEditJadwalList');
  const editJadwalBtn = document.getElementById('dpEditJadwalBtn');

  function openModal(el){ if(el) el.style.display = 'flex'; }
  function closeModal(el){ if(el) el.style.display = 'none'; }

  // close modal kalau klik overlay
  window.addEventListener('click', (e) => {
    if (e.target.classList?.contains('modal-overlay')) {
      closeModal(e.target);
    }
  });

  function setSectionEnabled(sectionEl, enabled){
    if (!sectionEl) return;
    const fields = sectionEl.querySelectorAll('input, select, textarea, button');
    fields.forEach(el => {
      // jangan disable tombol submit utama form
      if (el.classList?.contains('modal-btn')) return;

      // simpan required asli
      if (el.hasAttribute('required')) el.dataset.wasRequired = '1';

      if (enabled) {
        el.disabled = false;
        if (el.dataset.wasRequired === '1') el.setAttribute('required', 'required');
      } else {
        el.disabled = true;
        el.removeAttribute('required');
      }
    });
  }

  function syncTambahTipe(){
    const tipe = tambahTipe.value;

    if (tipe === 'dokter'){
      tambahDokterBox.style.display = '';
      tambahPemeriksaBox.style.display = 'none';

      setSectionEnabled(tambahDokterBox, true);
      setSectionEnabled(tambahPemeriksaBox, false);

      formTambah.action = dokterStoreUrl;
    } else {
      tambahDokterBox.style.display = 'none';
      tambahPemeriksaBox.style.display = '';

      setSectionEnabled(tambahDokterBox, false);
      setSectionEnabled(tambahPemeriksaBox, true);

      formTambah.action = pemeriksaStoreUrl;
    }
  }

  // ========== TAMBAH ==========
  btnOpenTambah?.addEventListener('click', () => {
    // auto-generate ID (hidden)
    const idDokterEl = document.getElementById('dpTambahIdDokter');
    const idPemeriksaEl = document.getElementById('dpTambahIdPemeriksa');
    if (idDokterEl) idDokterEl.value = genId('D');
    if (idPemeriksaEl) idPemeriksaEl.value = genId('P');

    // reset jadwal dokter (form tambah)
    if (tambahJadwalList) {
      tambahJadwalList.innerHTML = '';
      addJadwalRow(tambahJadwalList, 'jadwal');
    }

    // default dokter
    if (tambahTipe) tambahTipe.value = 'dokter';
    syncTambahTipe();
    openModal(modalTambah);
  });

  tambahTipe?.addEventListener('change', syncTambahTipe);

  tambahJadwalBtn?.addEventListener('click', () => {
    addJadwalRow(tambahJadwalList, 'jadwal');
  });

  // ========== EDIT ==========
  document.querySelectorAll('.dp-edit-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const tipe = btn.dataset.tipe;
      const id   = btn.dataset.id;

      editTipe.value = tipe;
      editJadwalList.innerHTML = '';

      if (tipe === 'dokter'){
        editDokterBox.style.display = '';
        editPemeriksaBox.style.display = 'none';
        setSectionEnabled(editDokterBox, true);
        setSectionEnabled(editPemeriksaBox, false);

        document.getElementById('dpEditNamaDokter').value = btn.dataset.nama || '';
        document.getElementById('dpEditJenisDokter').value = btn.dataset.jenis || '';
        document.getElementById('dpEditStatusDokter').value = btn.dataset.status || 'Aktif';

        formEdit.action = dokterUpdateBase + '/' + id;

        // autofill jadwal dari row
        const row = btn.closest('.dp-row');
        const raw = row?.dataset?.jadwal || '';
        if (raw.trim()) {
          raw.split(';;').filter(Boolean).forEach((s) => {
            const [hari, mulai, selesai] = s.split('|');
            addJadwalRow(editJadwalList, 'jadwal', hari, mulai, selesai);
          });
        } else {
          addJadwalRow(editJadwalList, 'jadwal');
        }

      } else {
        editDokterBox.style.display = 'none';
        editPemeriksaBox.style.display = '';
        setSectionEnabled(editDokterBox, false);
        setSectionEnabled(editPemeriksaBox, true);


        document.getElementById('dpEditNamaPemeriksa').value = btn.dataset.nama || '';
        document.getElementById('dpEditStatusPemeriksa').value = btn.dataset.status || 'Aktif';

        formEdit.action = pemeriksaUpdateBase + '/' + id;
      }

      openModal(modalEdit);
    });
  });

  editJadwalBtn?.addEventListener('click', () => {
    addJadwalRow(editJadwalList, 'jadwal');
  });

  // ========== HAPUS (konfirmasi) ==========
  document.querySelectorAll('form.js-dp-delete').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      Swal.fire({
        title: 'Hapus data ini?',
        text: 'Data dokter/pemeriksa akan dihapus dari daftar.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
      }).then((r) => { if (r.isConfirmed) form.submit(); });
    });
  });

  // ========== LIHAT JADWAL (TANPA JSON / TANPA ROUTE) ==========
  document.querySelectorAll('.dp-jadwal-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const tipe = btn.dataset.tipe || '';
      const raw = btn.dataset.jadwal || '';

      let html = '-';
      if (raw.trim()) {
        html = raw
          .split(';;')
          .filter(Boolean)
          .map((s) => {
            const [hari, mulai, selesai] = s.split('|');
            return `<div>${hari}: ${String(mulai).substring(0,5)} - ${String(selesai).substring(0,5)}</div>`;
          })
          .join('');
      } else {
        html = (tipe === 'dokter') ? '<div>-</div>' : html;
      }

      Swal.fire({ title: 'Jadwal', html, icon: 'info' });
    });
  });

  // helper jadwal row
  function addJadwalRow(container, baseName, hari = '', jamMulai = '', jamSelesai = ''){
    const idx = container.querySelectorAll('.dp-jrow').length;

    const row = document.createElement('div');
    row.className = 'dp-jrow';
    row.innerHTML = `
      <input class="dp-jinput" type="text" name="${baseName}[${idx}][hari]" placeholder="Hari" value="${hari || ''}" required>
      <input class="dp-jinput" type="time" name="${baseName}[${idx}][jam_mulai]" value="${(jamMulai || '').substring(0,5)}" required>
      <span class="dp-jsep">-</span>
      <input class="dp-jinput" type="time" name="${baseName}[${idx}][jam_selesai]" value="${(jamSelesai || '').substring(0,5)}" required>
      <button type="button" class="dp-jremove" title="Hapus">x</button>
    `;

    row.querySelector('.dp-jremove').addEventListener('click', () => row.remove());
    container.appendChild(row);
  }
});

</script>

{{-- SWEETALERT TOAST (SAMA DENGAN ADMINPOLI) --}}
@if(session('success'))
<script>
  AdminPoliToast.fire({
    icon: 'success',
    title: @json(session('success'))
  });
</script>
@endif

@if(session('error'))
<script>
  AdminPoliToast.fire({
    icon: 'error',
    title: @json(session('error'))
  });
</script>
@endif

@endpush