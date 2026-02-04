@extends('layouts.adminpoli')

@section('title', 'Edit Hasil Pemeriksaan Pasien')

@section('content')
@php
  $isPoliklinik = (($pendaftaran->tipe_pasien ?? '') === 'poliklinik');
  $awalJenis = ($pendaftaran->jenis_pemeriksaan ?? '');

  // label petugas awal (readonly display)
  $petugasLabel = '-';
  $petugasType  = null; // dokter | pemeriksa
  $petugasId    = null;

  if (!empty($pendaftaran->id_dokter)) {
      $d = collect($dokter)->firstWhere('id_dokter', $pendaftaran->id_dokter);
      $petugasLabel = $d ? ($d->nama.' ('.$d->jenis_dokter.')') : '(Dokter tidak ditemukan)';
      $petugasType = 'dokter';
      $petugasId   = $pendaftaran->id_dokter;
  } elseif (!empty($pendaftaran->id_pemeriksa)) {
      $p = collect($pemeriksa)->firstWhere('id_pemeriksa', $pendaftaran->id_pemeriksa);
      $petugasLabel = $p ? $p->nama_pemeriksa : '(Pemeriksa tidak ditemukan)';
      $petugasType = 'pemeriksa';
      $petugasId   = $pendaftaran->id_pemeriksa;
  }

  /**
   * Kamu butuh list penyakit yang sudah tersimpan untuk render awal.
   * Idealnya controller ngirim: $penyakitDetail = collection of:
   *   - id_diagnosa
   *   - diagnosa (nama penyakit)
   *   - id_nb (kode K3)
   *
   * Kalau belum ada, minimal kirim array $penyakitTerpilih berisi diagnosa string,
   * tapi untuk edit yang rapi, mending kirim $penyakitDetail.
   */
@endphp

<div class="ap-page periksa-page pemeriksaan-page">
  <div class="ap-topbar">
    <a href="{{ route('adminpoli.pemeriksaan.index') }}" class="ap-back-inline">
      <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="kembali">
    </a>
    <h1 class="ap-title">Edit Hasil Pemeriksaan Pasien</h1>
  </div>

  <div class="ap-card-form">
    <div class="ap-form-head">
      <div class="ap-form-head__title">Formulir Hasil Pemeriksaan</div>
      <div class="ap-form-head__sub">Pasien Poliklinik PT PLN Indonesia Power UBP Mrica</div>
    </div>

    @if ($errors->any())
      <div style="background:#ffecec;border:1px solid #ffb3b3;padding:12px;border-radius:10px;margin-bottom:12px;">
        <b>Gagal submit:</b>
        <ul style="margin:8px 0 0 18px;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('adminpoli.pemeriksaan.update', $pendaftaran->id_pendaftaran) }}" id="formPemeriksaan">
      @csrf
      @method('PUT')

      <div id="pendaftaranMeta"
        data-tipe="{{ $pendaftaran->tipe_pasien }}"
        data-awal-jenis="{{ $awalJenis }}"
        data-petugas-type="{{ $petugasType }}"
        data-petugas-id="{{ $petugasId }}"
        style="display:none;"></div>

      {{-- ===== DATA PEMERIKSAAN (READONLY) ===== --}}
      <div style="color:#316BA1;font-size:19px;margin:18px 0 10px;">
        Data Pemeriksaan Kesehatan
      </div>

      <div class="ap-vitals-grid">
        {{-- BARIS 1 --}}
        <div class="ap-vital-item">
          <div class="ap-vital-label">Sistol</div>
          <input class="ap-vital-input" type="number" step="any" name="sistol"
            value="{{ old('sistol', $hasil->sistol ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Diastol</div>
          <input class="ap-vital-input" type="number" step="any" name="diastol"
            value="{{ old('diastol', $hasil->diastol ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Denyut Nadi</div>
          <input class="ap-vital-input" type="number" step="any" name="nadi"
            value="{{ old('nadi', $hasil->nadi ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Puasa</div>
          <input class="ap-vital-input" type="number" step="any" name="gula_puasa"
            value="{{ old('gula_puasa', $hasil->gd_puasa ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>2 jam PP</div>
          <input class="ap-vital-input" type="number" step="any" name="gula_2jam_pp"
            value="{{ old('gula_2jam_pp', $hasil->gd_duajam ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Sewaktu</div>
          <input class="ap-vital-input" type="number" step="any" name="gula_sewaktu"
            value="{{ old('gula_sewaktu', $hasil->gd_sewaktu ?? '') }}" readonly>
        </div>

        {{-- BARIS 2 --}}
        <div class="ap-vital-item">
          <div class="ap-vital-label">Asam Urat</div>
          <input class="ap-vital-input" type="number" step="any" name="asam_urat"
            value="{{ old('asam_urat', $hasil->asam_urat ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Cholesterol</div>
          <input class="ap-vital-input" type="number" step="any" name="cholesterol"
            value="{{ old('cholesterol', $hasil->chol ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Trigliseride</div>
          <input class="ap-vital-input" type="number" step="any" name="trigliseride"
            value="{{ old('trigliseride', $hasil->tg ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Suhu</div>
          <input class="ap-vital-input" type="number" step="any" name="suhu"
            value="{{ old('suhu', $hasil->suhu ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Berat Badan</div>
          <input class="ap-vital-input" type="number" step="any" name="berat_badan"
            value="{{ old('berat_badan', $hasil->berat ?? '') }}" readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Tinggi Badan</div>
          <input class="ap-vital-input" type="number" step="any" name="tinggi_badan"
            value="{{ old('tinggi_badan', $hasil->tinggi ?? '') }}" readonly>
        </div>
      </div>

      {{-- ===== DIAGNOSA (EDITABLE) ===== --}}
      <div style="color:#316BA1;font-size:19px;margin:22px 0 10px;">Diagnosa</div>

      <div class="ap-row">
        <div class="ap-label">Penyakit</div><div class="ap-colon">:</div>
        <div class="ap-input">
          <select id="inpPenyakit" class="ap-select">
            <option value="">-- pilih (boleh kosong) --</option>
            @foreach($penyakit as $p)
              <option value="{{ $p->id_diagnosa }}"
                      data-nb="{{ $p->id_nb ?? '' }}"
                      data-k3="{{ $p->nama_k3 ?? '' }}">
                {{ $p->diagnosa }}
              </option>
            @endforeach
          </select>

          <button type="button" id="btnAddPenyakit" class="ap-btn-small">Tambah Penyakit</button>

          <div id="penyakitWrap" style="margin-top:10px;">
            {{-- Render penyakit existing (butuh $penyakitDetail dari controller) --}}
            @if(isset($penyakitDetail) && count($penyakitDetail))
              @foreach($penyakitDetail as $pd)
                <div class="penyakit-item" style="border:1px solid #e5e7eb;border-radius:10px;padding:10px;margin-top:10px;">
                  <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;">
                    <div class="penyakit-name" style="font-weight:600;">{{ $pd->diagnosa ?? '-' }}</div>
                    <button type="button" class="btnDelPenyakit ap-btn ap-btn--danger">Hapus</button>
                  </div>

                  <div style="display:grid;grid-template-columns:140px 1fr;gap:10px;margin-top:10px;align-items:center;">
                    <div style="color:#6b7280;">Kode Diagnosa K3</div>
                    <input type="text" name="id_nb[]" class="ap-input id-nb-input"
                           value="{{ old('id_nb.'.$loop->index, $pd->id_nb ?? '') }}"
                           placeholder="Masukkan ID NB..." />
                  </div>

                  <input type="hidden" name="penyakit_id[]" class="penyakit-id-hidden"
                         value="{{ old('penyakit_id.'.$loop->index, $pd->id_diagnosa ?? '') }}">
                </div>
              @endforeach
            @endif
          </div>

          {{-- template card penyakit --}}
          <div id="penyakitTemplate" style="display:none;">
            <div class="penyakit-item" style="border:1px solid #e5e7eb;border-radius:10px;padding:10px;margin-top:10px;">
              <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;">
                <div class="penyakit-name" style="font-weight:600;"></div>
                <button type="button" class="btnDelPenyakit ap-btn ap-btn--danger">Hapus</button>
              </div>

              <div style="display:grid;grid-template-columns:140px 1fr;gap:10px;margin-top:10px;align-items:center;">
                <div style="color:#6b7280;">Kode Diagnosa K3</div>
                <input type="text" name="id_nb[]" class="ap-input id-nb-input" placeholder="Masukkan ID NB..." />
              </div>

              <input type="hidden" name="penyakit_id[]" class="penyakit-id-hidden" value="">
            </div>
          </div>
        </div>
      </div>

      <div class="ap-row">
        <div class="ap-label">Saran</div><div class="ap-colon">:</div>
        <div class="ap-input">
          <select id="inpSaran" class="ap-select">
            <option value="">-- pilih (boleh kosong) --</option>
            @foreach($saran as $s)
              <option value="{{ $s->id_saran }}">
                {{ $s->kategori_saran ?? $s->id_saran }}
              </option>
            @endforeach
          </select>

          <button type="button" id="btnAddSaran" class="ap-btn-small">Tambah Saran</button>

          {{-- CHIP YANG SUDAH DIPILIH --}}
          <div id="chipSaran" style="margin-top:10px;">
            @if(isset($saranDetail) && count($saranDetail))
              @foreach($saranDetail as $sd)
                <span class="chip-saran"
                      data-value="{{ $sd->id_saran }}"
                      style="display:inline-flex;align-items:center;gap:6px;background:#eef3ff;border:1px solid #c7d7f5;color:#316BA1;padding:4px 8px;border-radius:10px;margin:3px 6px 0 0;font-size:13px;">
                  <span>{{ $sd->kategori_saran ?? $sd->id_saran }}</span>
                  <button type="button" class="chip-del"
                          style="border:none;background:transparent;cursor:pointer;font-weight:700;color:#316BA1;font-size:14px;line-height:1;">×</button>
                </span>
              @endforeach
            @endif
          </div>

          {{-- HIDDEN INPUT YANG SUDAH DIPILIH (INI YG KEKIRIM KE CONTROLLER) --}}
          <div id="hiddenSaran">
            @if(isset($saranDetail) && count($saranDetail))
              @foreach($saranDetail as $sd)
                <input type="hidden" name="id_saran[]" value="{{ $sd->id_saran }}">
              @endforeach
            @endif
          </div>
        </div>
      </div>

      {{-- 1 BARIS SAJA: Dokter/Pemeriksa (editable conditional) --}}
      <div class="ap-row" style="margin-top:10px;">
        <div class="ap-label">Dokter / Pemeriksa</div>
        <div class="ap-colon">:</div>

        <div class="ap-input" id="petugasRow">
          <span id="petugasDisplay" style="display:inline-block;">
            {{ $petugasLabel }}
          </span>

          <select name="petugas_after_obat" id="petugasAfterObat" class="ap-select" style="display:none;">
            <option value="">-- pilih dokter --</option>
            @foreach($dokter as $d)
              <option value="dokter:{{ $d->id_dokter }}">
                {{ $d->nama }} ({{ $d->jenis_dokter }})
              </option>
            @endforeach
          </select>

          @error('petugas_after_obat')
            <div style="color:#d00;font-size:12px;margin-top:6px;">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- ===== OBAT & HARGA (EDITABLE) ===== --}}
      <div style="color:#316BA1;font-size:19px;margin:22px 0 10px;">Obat & Harga</div>

      <div class="obat-header">
        <div>Nama Obat</div>
        <div>Jumlah</div>
        <div>Satuan</div>
        <div>Harga Satuan</div>
        <div>Subtotal</div>
      </div>

      {{-- TEMPLATE ROW OBAT (hidden) --}}
      <div id="obatTemplate" style="display:none;">
        <div class="obat-row">
          <select name="obat_id[]" class="ap-select obat-select">
            <option value="">pilih obat</option>
            @foreach($obat as $o)
              <option value="{{ $o->id_obat }}"
                data-harga="{{ $o->harga ?? 0 }}"
                data-satuan="{{ $o->satuan ?? '' }}">
                {{ $o->nama_obat }}
              </option>
            @endforeach
          </select>

          <input name="jumlah[]" type="number" min="1" placeholder="Jumlah" class="obat-jumlah">
          <input name="satuan[]" type="text" list="satuanList" placeholder="Satuan" class="obat-satuan">

          <input type="hidden" name="harga_satuan[]" class="obat-harga-raw">
          <input type="text" class="ap-input obat-harga" placeholder="Harga Satuan" readonly>

          <input type="hidden" class="obat-subtotal-raw">
          <input type="text" class="ap-input obat-subtotal" placeholder="Subtotal" readonly>

          <button type="button" class="btnDel obat-hapus">Hapus</button>
        </div>
      </div>

      <div id="obatWrap" class="periksa-page">
        <datalist id="satuanList">
          <option value="Tablet"></option>
          <option value="Botol"></option>
          <option value="Box"></option>
          <option value="Flexpen"></option>
          <option value="Bungkus/puyer"></option>
          <option value="Vial"></option>
          <option value="Ampul"></option>
        </datalist>

        @if(isset($detailResep) && $detailResep->count())
          @foreach($detailResep as $dr)
            <div class="obat-row">
              <select class="obat-select" name="obat_id[]">
                <option value="">-- pilih obat (boleh kosong) --</option>
                @foreach($obat as $o)
                  <option value="{{ $o->id_obat }}"
                          data-harga="{{ $o->harga ?? 0 }}"
                          data-satuan="{{ $o->satuan ?? '' }}"
                          {{ $o->id_obat == $dr->id_obat ? 'selected' : '' }}>
                    {{ $o->nama_obat }}
                  </option>
                @endforeach
              </select>

              <input class="obat-jumlah" type="number" min="1" name="jumlah[]" value="{{ (int)($dr->jumlah ?? 1) }}">
              <input class="obat-satuan" type="text" name="satuan[]" value="{{ $dr->satuan ?? '' }}">

              <input class="obat-harga-raw" type="hidden" name="harga_satuan[]" value="{{ (float)($dr->harga_satuan ?? 0) }}">
              <input class="obat-subtotal-raw" type="hidden" value="{{ (float)($dr->subtotal ?? 0) }}">

              <input class="obat-harga" type="text" value="" readonly>
              <input class="obat-subtotal" type="text" value="" readonly>

              <button class="obat-hapus" type="button">Hapus</button>
            </div>
          @endforeach
        @endif
      </div>

      <button type="button" id="btnAddObat" class="ap-btn-small">Tambah Obat/Alkes</button>

      <div class="total-harga" style="text-align:right;margin-top:10px;font-weight:600;color:#787676;">
        Total : <strong id="totalHarga">Rp0</strong>
      </div>

      <button class="ap-register" type="submit" style="margin-top:18px;">Submit</button>
    </form>
  </div>

  <footer class="ap-footer">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </footer>
</div>

<script>
  // ===== helper rupiah =====
  function rupiah(n){
    n = Number(n || 0);
    return 'Rp' + n.toLocaleString('id-ID');
  }

  function showWarn(title, text, cb) {
    if (window.Swal && Swal.fire) {
      Swal.fire({
        icon: 'warning',
        title,
        text,
        confirmButtonText: 'OK',
        confirmButtonColor: '#316BA1',
        heightAuto: false,
        scrollbarPadding: false
      }).then(() => cb?.());
    } else {
      alert(title + "\n\n" + text);
      cb?.();
    }
  }

  function bindChipDelete(root=document){
    root.querySelectorAll('.chip-saran .chip-del').forEach(btn => {
      btn.addEventListener('click', () => {
        const chip = btn.closest('.chip-saran');
        const value = chip?.dataset?.value;

        // hapus hidden input yang valuenya sama
        const hidden = document.querySelector(`#hiddenSaran input[name="id_saran[]"][value="${CSS.escape(value)}"]`);
        if(hidden) hidden.remove();

        // hapus chip
        if(chip) chip.remove();
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    bindChipDelete(document);
  });

  document.getElementById('btnAddSaran')?.addEventListener('click', () => {
    const sel = document.getElementById('inpSaran');
    if(!sel || !sel.value) return;

    const value = sel.value;
    const label = sel.options[sel.selectedIndex]?.text || value;

    // cegah dobel
    const exists = !!document.querySelector(`#hiddenSaran input[name="id_saran[]"][value="${CSS.escape(value)}"]`);
    if(exists){
      sel.value = '';
      return;
    }

    // buat chip
    const chip = document.createElement('span');
    chip.className = 'chip-saran';
    chip.dataset.value = value;
    chip.style.cssText =
      'display:inline-flex;align-items:center;gap:6px;background:#eef3ff;border:1px solid #c7d7f5;' +
      'color:#316BA1;padding:4px 8px;border-radius:10px;margin:3px 6px 0 0;font-size:13px;';
    chip.innerHTML =
      `<span>${label}</span>` +
      `<button type="button" class="chip-del" style="border:none;background:transparent;cursor:pointer;font-weight:700;color:#316BA1;font-size:14px;line-height:1;">×</button>`;

    // buat hidden input
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'id_saran[]';
    hidden.value = value;

    document.getElementById('chipSaran').appendChild(chip);
    document.getElementById('hiddenSaran').appendChild(hidden);

    bindChipDelete(chip);

    sel.value = '';
  });

  // =========================
  // PENYAKIT (sync create)
  // =========================
  function alreadyAddedPenyakit(id){
    return !!document.querySelector(`#penyakitWrap input.penyakit-id-hidden[value="${CSS.escape(id)}"]`);
  }

  function bindDeletePenyakit(node){
    node.querySelector('.btnDelPenyakit')?.addEventListener('click', () => node.remove());
  }

  // bind delete untuk penyakit existing (render server)
  document.querySelectorAll('#penyakitWrap .penyakit-item').forEach(bindDeletePenyakit);


  document.getElementById('btnAddPenyakit')?.addEventListener('click', async () => {
    const sel = document.getElementById('inpPenyakit');
    if(!sel || !sel.value) return;

    const id = sel.value;
    if(alreadyAddedPenyakit(id)){
      sel.value = '';
      return;
    }

    const opt = sel.options[sel.selectedIndex];
    const label = opt ? opt.text : 'Penyakit';
    const defaultNb = (opt?.dataset?.nb || '').trim();

    // modal input id_nb
    const { value: nb } = await Swal.fire({
      title: 'Input ID NB',
      text: `Penyakit: ${label}`,
      input: 'text',
      inputValue: defaultNb,
      inputPlaceholder: 'Masukkan ID NB...',
      showCancelButton: true,
      confirmButtonText: 'Simpan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#316BA1',
      heightAuto: false,
      scrollbarPadding: false,
      inputValidator: (v) => {
        if (!v || !v.trim()) return 'ID NB wajib diisi.';
        return null;
      }
    });

    if(!nb) return; // batal

    const tpl = document.querySelector('#penyakitTemplate .penyakit-item');
    if(!tpl) return;

    const node = tpl.cloneNode(true);
    node.querySelector('.penyakit-name').textContent = label;
    node.querySelector('.penyakit-id-hidden').value = id;
    node.querySelector('.id-nb-input').value = nb.trim();

    bindDeletePenyakit(node);

    document.getElementById('penyakitWrap').appendChild(node);
    sel.value = '';
  });

  // =========================
  // OBAT (sync create)
  // =========================
  const obatWrap = document.getElementById('obatWrap');
  const totalHarga = document.getElementById('totalHarga');

  function hitungSubtotal(row){
    const qty = Number(row.querySelector('.obat-jumlah')?.value || 0);
    const hargaRawEl = row.querySelector('.obat-harga-raw');
    const subRawEl   = row.querySelector('.obat-subtotal-raw');

    const harga = Number(hargaRawEl?.value || 0);
    const subtotal = qty * harga;

    if (subRawEl) subRawEl.value = subtotal;

    const hargaEl = row.querySelector('.obat-harga');
    const subEl   = row.querySelector('.obat-subtotal');

    if (hargaEl) hargaEl.value = rupiah(harga);
    if (subEl) subEl.value = rupiah(subtotal);
  }

  function hitungTotal(){
    let total = 0;
    document.querySelectorAll('#obatWrap .obat-row').forEach(row => {
      total += Number(row.querySelector('.obat-subtotal-raw')?.value || 0);
    });
    if (totalHarga) totalHarga.textContent = rupiah(total);
  }

  function adaObatTerpilih(){
    return [...document.querySelectorAll('select[name="obat_id[]"]')]
      .some(s => (s.value || '').trim() !== '');
  }

  function togglePetugasEditable(hasObat) {
    const meta = document.getElementById('pendaftaranMeta');
    const tipe = meta ? (meta.dataset.tipe || '') : '';
    const awalJenis = meta ? (meta.dataset.awalJenis || '') : '';

    // cuma kasus ini yang butuh pilih dokter
    const shouldEditable = (tipe !== 'poliklinik' && awalJenis === 'cek_kesehatan' && hasObat);

    const display = document.getElementById('petugasDisplay');
    const select  = document.getElementById('petugasAfterObat');

    if (!display || !select) return;

    if (shouldEditable) {
      display.style.display = 'none';
      select.style.display = 'inline-block';
    } else {
      display.style.display = 'inline-block';
      select.style.display = 'none';
      select.value = '';
    }
  }

  function refreshPetugasUI(){
    togglePetugasEditable(adaObatTerpilih());
  }

  function bindRowEvents(row){
    const select = row.querySelector('.obat-select');
    const qtyEl  = row.querySelector('.obat-jumlah');
    const satuanEl = row.querySelector('.obat-satuan');

    if(select){
      select.addEventListener('change', () => {
        const opt = select.options[select.selectedIndex];
        const harga = Number(opt?.dataset?.harga || 0);
        const satuan = opt?.dataset?.satuan || '';

        row.querySelector('.obat-harga-raw').value = harga;

        if(satuanEl && !satuanEl.value){
          satuanEl.value = satuan;
        }

        hitungSubtotal(row);
        hitungTotal();
        refreshPetugasUI();
      });
    }

    if(qtyEl){
      qtyEl.addEventListener('input', () => {
        hitungSubtotal(row);
        hitungTotal();
      });
    }

    row.querySelector('.obat-hapus')?.addEventListener('click', () => {
      row.remove();
      hitungTotal();
      refreshPetugasUI();
    });

    // init format
    hitungSubtotal(row);
  }

  // init rows existing
  document.querySelectorAll('#obatWrap .obat-row').forEach(bindRowEvents);
  hitungTotal();
  refreshPetugasUI();

  document.getElementById('btnAddObat')?.addEventListener('click', () => {
    const tpl = document.querySelector('#obatTemplate .obat-row');
    if(!tpl) return;

    const row = tpl.cloneNode(true);

    row.querySelector('.obat-select').value = '';
    row.querySelector('.obat-jumlah').value = 1;
    row.querySelector('.obat-satuan').value = '';
    row.querySelector('.obat-harga-raw').value = 0;
    row.querySelector('.obat-subtotal-raw').value = 0;
    row.querySelector('.obat-harga').value = '';
    row.querySelector('.obat-subtotal').value = '';

    obatWrap.appendChild(row);
    bindRowEvents(row);
    hitungTotal();
    refreshPetugasUI();
  });

  // anti duplikat obat
  function getPickedObatValues(exceptRow = null){
    const vals = [];
    document.querySelectorAll('#obatWrap .obat-row').forEach(r => {
      if (exceptRow && r === exceptRow) return;
      const sel = r.querySelector('select[name="obat_id[]"]');
      const v = sel ? sel.value : '';
      if (v) vals.push(v);
    });
    return vals;
  }

  let obatDuplicateAlertOpen = false;

  document.addEventListener('change', function(e){
    if(!e.target.classList.contains('obat-select')) return;

    const row = e.target.closest('.obat-row');
    const pickedOther = new Set(getPickedObatValues(row));
    const val = e.target.value;

    if(val && pickedOther.has(val)){
      if(e.target.tomselect){
        e.target.tomselect.clear(true);
      } else {
        e.target.value = '';
      }

      // reset row
      row.querySelector('.obat-satuan').value = '';
      row.querySelector('.obat-harga-raw').value = 0;
      row.querySelector('.obat-harga').value = '';
      row.querySelector('.obat-subtotal-raw').value = 0;
      row.querySelector('.obat-subtotal').value = '';

      hitungTotal();
      refreshPetugasUI();

      if(!obatDuplicateAlertOpen){
        obatDuplicateAlertOpen = true;
        Swal.fire({
          icon: 'warning',
          title: 'Obat sudah dipilih',
          text: 'Obat yang sama tidak boleh dipilih dua kali.',
          confirmButtonText: 'OK',
          confirmButtonColor: '#316BA1',
          heightAuto: false,
          scrollbarPadding: false
        }).then(() => {
          obatDuplicateAlertOpen = false;
          setTimeout(() => e.target.focus(), 50);
        });
      }
    }
  });

  // =========================
  // SUBMIT VALIDATION (sync create)
  // =========================
  document.getElementById('formPemeriksaan')?.addEventListener('submit', function (e) {
    const meta = document.getElementById('pendaftaranMeta');
    const tipe = meta ? (meta.dataset.tipe || '') : '';
    const awalJenis = meta ? (meta.dataset.awalJenis || '') : '';
    const adaObat = adaObatTerpilih();

    // non poliklinik + awal cek_kesehatan + ada obat => dokter wajib dipilih
    if (tipe !== 'poliklinik' && awalJenis === 'cek_kesehatan' && adaObat) {
      const dokter = document.getElementById('petugasAfterObat');
      if (dokter && !dokter.value) {
        e.preventDefault();
        showWarn('Dokter belum dipilih', 'Jika awalnya cek kesehatan lalu ditambah obat, wajib pilih dokter.', () => dokter.focus());
        return;
      }
    }

    // satuan wajib jika obat dipilih
    let firstInvalidSatuan = null;

    document.querySelectorAll('#obatWrap .obat-row').forEach((row) => {
      const obatSelect = row.querySelector('select[name="obat_id[]"]');
      const satuanInput = row.querySelector('input[name="satuan[]"]');
      if (!obatSelect || !satuanInput) return;

      const obatVal = obatSelect.value;
      const satuanVal = (satuanInput.value ?? '').trim();

      if (obatVal && satuanVal === '' && !firstInvalidSatuan) {
        firstInvalidSatuan = satuanInput;
      }
    });

    if (firstInvalidSatuan) {
      e.preventDefault();
      showWarn('Satuan belum diisi', 'Jika obat dipilih, satuan wajib diisi.', () => firstInvalidSatuan.focus());
      return;
    }
  });
</script>
@endsection