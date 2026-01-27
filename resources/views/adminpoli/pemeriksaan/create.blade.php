@extends('layouts.adminpoli')

@section('title', 'Input Hasil Pemeriksaan Pasien')

@section('content')
<div class="ap-page">

  <div class="ap-topbar">
    <a href="{{ route('adminpoli.dashboard') }}" class="ap-back-inline">
      <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="kembali">
    </a>
    <h1 class="ap-title">Input Hasil Pemeriksaan Pasien</h1>
  </div>

  <div class="ap-card-form">
    <div class="ap-form-head">
      <div class="ap-form-head__title">Formulir Hasil Pemeriksaan</div>
      <div class="ap-form-head__sub">Pasien Poliklinik PT PLN Indonesia Power UBP Mrica</div>
    </div>

    <form method="POST" action="{{ route('adminpoli.pemeriksaan.store', $pendaftaran->id_pendaftaran) }}" id="formPemeriksaan">
      @csrf

      {{-- ===== DATA PEMERIKSAAN ===== --}}  
    <div style="color:#316BA1;font-size:19px;margin:18px 0 10px;">
        Data Pemeriksaan Kesehatan
      </div>

      <div class="ap-vitals-grid">
        {{-- BARIS 1 --}}
        <div class="ap-vital-item">
          <div class="ap-vital-label">Sistol</div>
          <input class="ap-vital-input" type="number" step="any" name="sistol">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Diastol</div>
          <input class="ap-vital-input" type="number" step="any" name="diastol">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Denyut Nadi</div>
          <input class="ap-vital-input" type="number" step="any" name="nadi">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Puasa</div>
          <input class="ap-vital-input" type="number" step="any" name="gula_puasa">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>2 jam PP</div>
          <input class="ap-vital-input" type="number" step="any" name="gula_2jam_pp">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Sewaktu</div>
          <input class="ap-vital-input" type="number" step="any" name="gula_sewaktu">
        </div>

        {{-- BARIS 2 --}}
        <div class="ap-vital-item">
          <div class="ap-vital-label">Asam Urat</div>
          <input class="ap-vital-input" type="number" step="any" name="asam_urat">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Cholesterol</div>
          <input class="ap-vital-input" type="number" step="any" name="cholesterol">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Trigliseride</div>
          <input class="ap-vital-input" type="number" step="any" name="trigliseride">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Suhu</div>
          <input class="ap-vital-input" type="number" step="any" name="suhu">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Berat Badan</div>
          <input class="ap-vital-input" type="number" step="any" name="berat_badan">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Tinggi Badan</div>
          <input class="ap-vital-input" type="number" step="any" name="tinggi_badan">
        </div>
      </div>

      {{-- ===== DIAGNOSA ===== --}}
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

              <div id="chipPenyakit" style="margin-top:10px;"></div>
              <div id="hiddenPenyakit"></div>
          </div>
        </div>

      <div class="ap-row">
        <div class="ap-label">Saran</div><div class="ap-colon">:</div>
        <div class="ap-input">
          <select id="inpSaran" class="ap-select">
            <option value="">-- pilih (boleh kosong) --</option>
            @foreach($saran as $s)
                <option value="{{ $s->id_saran }}" data-diagnosa="{{ $s->id_diagnosa }}">{{ $s->saran }}</option>
            @endforeach
          </select>

          <button type="button" id="btnAddSaran" class="ap-btn-small">Tambah Saran</button>

          <div id="chipSaran" style="margin-top:10px;"></div>
          <div id="hiddenSaran"></div>
        </div>
      </div>

      {{-- ===== OBAT & HARGA ===== --}}
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
            <option value="">pilih obat </option>
            @foreach($obat as $o)
              <option value="{{ $o->id_obat }}"
                data-harga="{{ $o->harga ?? 0 }}"
                data-satuan="{{ $o->satuan ?? '' }}">
                {{ $o->nama_obat }}
              </option>
            @endforeach
          </select>

          <input name="jumlah[]" type="number" min="1" placeholder="Jumlah" class="obat-jumlah">
          <input name="satuan[]" type="text" placeholder="Satuan" class="obat-satuan">
          <input type="hidden" name="harga_satuan[]" class="obat-harga-raw">
          <input type="text" class="ap-input obat-harga" placeholder="Harga Satuan">

          <input type="hidden" class="obat-subtotal-raw">
          <input type="text" class="ap-input obat-subtotal" placeholder="Subtotal" readonly>

          <button type="button" class="btnDel obat-hapus">Hapus</button>
        </div>
      </div>

      <div id="obatWrap"></div>

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

  // ===== Helper Numeric =====
  function isNumeric(val){
    return val !== '' && !isNaN(val) && isFinite(val);
  }

  // ===== CHIP UTILITY =====
  function addChip({value, label, chipContainer, hiddenContainer, inputName}) {
    if(!value) return;

    const exists = [...hiddenContainer.querySelectorAll(`input[name="${inputName}[]"]`)]
      .some(i => i.value == value);
    if(exists) return;

    const chip = document.createElement('span');
    chip.style.cssText =
    'display:inline-flex;align-items:center;gap:6px;background:#eef3ff;border:1px solid #c7d7f5;' +
    'color:#316BA1;padding:4px 8px;border-radius:10px;margin:3px 6px 0 0;font-size:13px;';

    chip.innerHTML =
    `<span>${label}</span>` +
    `<button type="button" style="border:none;background:transparent;cursor:pointer;font-weight:700;color:#316BA1;font-size:14px;line-height:1;">×</button>`;

    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = `${inputName}[]`;
    hidden.value = value;

    chip.querySelector('button').addEventListener('click', () => {
      chip.remove();
      hidden.remove();

      if (inputName === 'penyakit_id') filterSaran();
    });

    chipContainer.appendChild(chip);
    hiddenContainer.appendChild(hidden);
  }
  function getSelectedPenyakitIds(){
    return [...document.querySelectorAll('#hiddenPenyakit input[name="penyakit_id[]"]')].map(i => i.value);
  }

  function filterSaran(){
    const selected = new Set(getSelectedPenyakitIds());
    const saranSelect = document.getElementById('inpSaran');

    [...saranSelect.options].forEach((opt, idx) => {
      if(idx === 0) return; // keep placeholder
      const diagId = opt.dataset.diagnosa;
      opt.hidden = selected.size > 0 ? !selected.has(diagId) : false;
    });

    // reset kalau pilihan jadi tidak valid
    if (saranSelect.selectedIndex > 0 && saranSelect.options[saranSelect.selectedIndex].hidden) {
      saranSelect.value = "";
    }
  }

  function filterSaranByDiagnosa(diagnosaId){
    const sel = document.getElementById('inpSaran');
    [...sel.options].forEach((opt, idx) => {
      if(idx === 0) return; // placeholder
      opt.hidden = diagnosaId ? (opt.dataset.diagnosa !== diagnosaId) : false;
    });
    sel.value = ""; // reset pilihan
  }

    // Penyakit (text)
  document.getElementById('btnAddPenyakit').addEventListener('click', () => {
    const sel = document.getElementById('inpPenyakit');
    if(!sel.value) return;

    addChip({
        value: sel.value,
        label: sel.options[sel.selectedIndex].text,
        chipContainer: document.getElementById('chipPenyakit'),
        hiddenContainer: document.getElementById('hiddenPenyakit'),
        inputName: 'penyakit_id'
    });

    filterSaran();

    sel.value = '';
  }); 

  // Saran (select)
  document.getElementById('btnAddSaran').addEventListener('click', () => {
    const sel = document.getElementById('inpSaran');
    if(!sel.value) return;
    addChip({
      value: sel.value,
      label: sel.options[sel.selectedIndex].text,
      chipContainer: document.getElementById('chipSaran'),
      hiddenContainer: document.getElementById('hiddenSaran'),
      inputName: 'id_saran'
    });
    sel.value = '';
  });

  // ===== OBAT LOGIC (TANPA JSON) =====
    // helper rupiah kamu
  function rupiah(n){
    n = Number(n || 0);
    return 'Rp' + n.toLocaleString('id-ID');
  }

  const obatWrap = document.getElementById('obatWrap');
  const totalHarga = document.getElementById('totalHarga'); // pastikan id ini ada

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
    obatWrap.querySelectorAll('.obat-row').forEach(row => {
      total += Number(row.querySelector('.obat-subtotal-raw')?.value || 0);
    });
    if (totalHarga) totalHarga.textContent = rupiah(total);
  }

  function bindRowEvents(row){
    const select = row.querySelector('.obat-select');
    const qtyEl  = row.querySelector('.obat-jumlah');
    const satuanEl = row.querySelector('.obat-satuan');

    // saat pilih obat: isi harga + satuan dari option data-*
    if(select){
      select.addEventListener('change', () => {
        const opt = select.options[select.selectedIndex];
        const harga = Number(opt?.dataset?.harga || 0);
        const satuan = opt?.dataset?.satuan || '';

        row.querySelector('.obat-harga-raw').value = harga;

        // satuan auto isi kalau belum diisi atau mau kamu paksa overwrite
        if(satuanEl && !satuanEl.value){
          satuanEl.value = satuan;
        }

        hitungSubtotal(row);
        hitungTotal();
      });
    }

    // saat qty berubah
    if(qtyEl){
      qtyEl.addEventListener('input', () => {
        hitungSubtotal(row);
        hitungTotal();
      });
    }

    // hapus row
    row.querySelector('.obat-hapus')?.addEventListener('click', () => {
      row.remove();
      hitungTotal();
    });

    // format awal (buat row dari DB)
    hitungSubtotal(row);
  }

  // bind semua row yang sudah dirender blade
  document.querySelectorAll('#obatWrap .obat-row').forEach(bindRowEvents);
  hitungTotal();

  // tombol tambah
  document.getElementById('btnAddObat')?.addEventListener('click', () => {
    const tpl = document.querySelector('#obatTemplate .obat-row');
    if(!tpl) return;

    const row = tpl.cloneNode(true);

    // reset default
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
  });

  // ambil semua value obat yang sudah kepilih (kecuali row yang sedang dicek)
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

  // alert anti spam (biar ga kebuka berkali-kali kalau user klik cepat)
  let obatDuplicateAlertOpen = false;

  document.addEventListener('change', function(e){
    if(!e.target.classList.contains('obat-select')) return;

    const row = e.target.closest('.obat-row');
    const pickedOther = new Set(getPickedObatValues(row));
    const val = e.target.value;

    // kalau dobel
    if(val && pickedOther.has(val)){
      // reset pilihan (support TomSelect & native)
      if(e.target.tomselect){
        e.target.tomselect.clear(true);
      } else {
        e.target.value = '';
      }

      // kosongin field row biar ga nyisa
      const satuan = row.querySelector('.obat-satuan');
      const hargaRaw = row.querySelector('.obat-harga-raw');
      const harga = row.querySelector('.obat-harga');
      const subRaw = row.querySelector('.obat-subtotal-raw');
      const sub = row.querySelector('.obat-subtotal');

      if(satuan) satuan.value = '';
      if(hargaRaw) hargaRaw.value = 0;
      if(harga) harga.value = '';
      if(subRaw) subRaw.value = 0;
      if(sub) sub.value = '';

      // update total kalau fungsi kamu ada
      if(typeof hitungTotal === 'function') hitungTotal();

      // alert (SweetAlert)
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
          // fokus balik ke select biar user enak ganti
          setTimeout(() => e.target.focus(), 50);
        });
      }

      return;
    }
  });

  document.getElementById('formPemeriksaan')?.addEventListener('submit', function (e) {
    const fields = [
      { name: 'sistol', label: 'Sistol' },
      { name: 'diastol', label: 'Diastol' },
      { name: 'nadi', label: 'Denyut Nadi' },
      { name: 'gula_puasa', label: 'Gula Darah Puasa' },
      { name: 'gula_2jam_pp', label: 'Gula Darah 2 Jam PP' },
      { name: 'gula_sewaktu', label: 'Gula Darah Sewaktu' },
      { name: 'asam_urat', label: 'Asam Urat' },
      { name: 'cholesterol', label: 'Cholesterol' },
      { name: 'trigliseride', label: 'Trigliseride' },
      { name: 'suhu', label: 'Suhu' },
      { name: 'berat_badan', label: 'Berat Badan' },
      { name: 'tinggi_badan', label: 'Tinggi Badan' },
    ];

    for (const f of fields) {
      const el = this.querySelector(`[name="${f.name}"]`);
      if (!el) continue;

      const val = (el.value ?? '').toString().trim();
      if (val === '') continue;

      if (!isFinite(Number(val))) {
        e.preventDefault();

        Swal.fire({
          icon: 'warning',
          title: 'Input Tidak Valid',
          text: `${f.label} harus berupa angka.`,
          confirmButtonText: 'OK',
          confirmButtonColor: '#316BA1',
          heightAuto: false,
          scrollbarPadding: false
        }).then(() => el.focus());

        return;
      }
    }
  });

  // ===== NAVIGASI KEYBOARD INPUT PEMERIKSAAN =====
  document.addEventListener('DOMContentLoaded', () => {
    const vitals = Array.from(
      document.querySelectorAll('.ap-vital-input')
    );

    if (vitals.length === 0) return;

    vitals.forEach((input, idx) => {
      input.addEventListener('keydown', (e) => {

        // ENTER → pindah ke input berikutnya (bukan submit)
        if (e.key === 'Enter') {
          e.preventDefault();
          vitals[idx + 1]?.focus();
          return;
        }

        // PANAH BAWAH → ke input berikutnya
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight'){
          e.preventDefault();
          vitals[idx + 1]?.focus();
          return;
        }

        // PANAH ATAS → ke input sebelumnya
        if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
          e.preventDefault();
          vitals[idx - 1]?.focus();
          return;
        }
      });
    });
  });

</script>
@endsection