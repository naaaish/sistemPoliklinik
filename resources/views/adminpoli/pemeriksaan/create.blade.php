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
          <input class="ap-vital-input" name="sistol">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Diastol</div>
          <input class="ap-vital-input" name="diastol">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Denyut Nadi</div>
          <input class="ap-vital-input" name="nadi">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Puasa</div>
          <input class="ap-vital-input" name="gula_puasa">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>2 jam PP</div>
          <input class="ap-vital-input" name="gula_2jam_pp">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Sewaktu</div>
          <input class="ap-vital-input" name="gula_sewaktu">
        </div>

        {{-- BARIS 2 --}}
        <div class="ap-vital-item">
          <div class="ap-vital-label">Asam Urat</div>
          <input class="ap-vital-input" name="asam_urat">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Cholesterol</div>
          <input class="ap-vital-input" name="cholesterol">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Trigliseride</div>
          <input class="ap-vital-input" name="trigliseride">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Suhu</div>
          <input class="ap-vital-input" name="suhu">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Berat Badan</div>
          <input class="ap-vital-input" name="berat_badan">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Tinggi Badan</div>
          <input class="ap-vital-input" name="tinggi_badan">
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
                <option value="{{ $p->id_diagnosa }}">{{ $p->diagnosa }}</option>
            @endforeach
            </select>

            <button type="button" id="btnAddPenyakit" class="ap-btn-small">Tambah Penyakit</button>

            <div id="chipPenyakit" style="margin-top:10px;"></div>
            <div id="hiddenPenyakit"></div>
        </div>
        </div>

      <div class="ap-row">
        <div class="ap-label">Diagnosa</div><div class="ap-colon">:</div>
        <div class="ap-input">
          <select id="inpDiagnosa" class="ap-select">
            <option value="">-- pilih (boleh kosong) --</option>
            @foreach($diagnosaK3 as $d)
              <option value="{{ $d->id_nb }}">{{ $d->nama_penyakit }}</option>
            @endforeach
          </select>

          <button type="button" class="ap-btn-small" id="btnAddDiagnosa" style="width:auto;padding:8px 12px;margin-top:8px;">
            Tambah Diagnosa
          </button>

          <div id="chipDiagnosa" style="margin-top:10px;"></div>
          <div id="hiddenDiagnosa"></div>
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
 
  // Diagnosa K3 (select)
  document.getElementById('btnAddDiagnosa').addEventListener('click', () => {
    const sel = document.getElementById('inpDiagnosa');
    if(!sel.value) return;
    addChip({
      value: sel.value,
      label: sel.options[sel.selectedIndex].text,
      chipContainer: document.getElementById('chipDiagnosa'),
      hiddenContainer: document.getElementById('hiddenDiagnosa'),
      inputName: 'diagnosa_k3_id'
    });
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
      inputName: 'saran_id'
    });
    sel.value = '';
  });

  // ===== OBAT LOGIC (TANPA JSON) =====
  const obatWrap = document.getElementById('obatWrap');
  const totalText = document.getElementById('totalText');

  function hitungTotal(){
    let total = 0;
    obatWrap.querySelectorAll('.obat-row').forEach(row => {
      const qty = Number(row.querySelector('[name="jumlah[]"]').value || 0);
      const harga = Number(row.querySelector('[name="harga_satuan[]"]').value || 0);
      const subtotal = qty * harga;

      row.querySelector('.subtotal').value = subtotal ? subtotal : '';
      total += subtotal;
    });
    totalText.textContent = rupiah(total);
  }

  function addObatRow(){
    const templateRow = document.querySelector('#obatTemplate .obat-row').cloneNode(true);
    obatWrap.appendChild(templateRow);
    const select = templateRow.querySelector('.obat-select');
    const qtyEl = templateRow.querySelector('[name="jumlah[]"]');
    const satuanEl = templateRow.querySelector('[name="satuan[]"]');
    const hargaEl = templateRow.querySelector('[name="harga_satuan[]"]');

    initObatSelect(select);

    select.addEventListener('change', () => {
      const opt = select.options[select.selectedIndex];
      hargaEl.value = opt.dataset.harga || '';
      satuanEl.value = opt.dataset.satuan || '';
      if(!qtyEl.value) qtyEl.value = 1;
      hitungTotal();
    });

    qtyEl.addEventListener('input', hitungTotal);
    hargaEl.addEventListener('input', hitungTotal);

    templateRow.querySelector('.btnDel').addEventListener('click', () => {
      templateRow.remove();
      hitungTotal();
    });

    hitungTotal();
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.btnDel, .btn-del, .btn-hapus, .obat-del, button[data-del="obat"]');
    if(!btn) return;

    const row = btn.closest('.obat-row');
    if(!row) return;

    const sel = row.querySelector('.obat-select');
    if(sel?.tomselect) sel.tomselect.destroy(); // penting biar gak nyisa

    row.remove();
    hitungTotal();
  });

  function getPickedObatValues(exceptRow=null){
    const rows = [...document.querySelectorAll('#obatWrap .obat-row')];
    return rows
      .filter(r => r !== exceptRow)
      .map(r => r.querySelector('.obat-select')?.value)
      .filter(v => v);
  }

  document.addEventListener('change', function(e){
    if(!e.target.classList.contains('obat-select')) return;

    const row = e.target.closest('.obat-row');
    const pickedOther = new Set(getPickedObatValues(row));
    const val = e.target.value;

    // kalau dobel, reset + kasih peringatan
    if(val && pickedOther.has(val)){
      if(e.target.tomselect){
        e.target.tomselect.clear(true);
      } else {
        e.target.value = '';
      }

      // kosongin field row biar ga nyisa
      row.querySelector('.obat-satuan') && (row.querySelector('.obat-satuan').value = '');
      row.querySelector('.obat-harga-raw') && (row.querySelector('.obat-harga-raw').value = '');
      row.querySelector('.obat-harga') && (row.querySelector('.obat-harga').value = '');
      row.querySelector('.obat-subtotal-raw') && (row.querySelector('.obat-subtotal-raw').value = 0);
      row.querySelector('.obat-subtotal') && (row.querySelector('.obat-subtotal').value = '');

      hitungTotal();

      Swal.fire({
        icon: 'warning',
        title: 'Obat sudah dipilih',
        text: 'Obat yang sama tidak boleh dipilih dua kali.',
        confirmButtonColor: '#316BA1',
        heightAuto: false,  
        scrollbarPadding: false 
      });

      return;
    }
  });

  document.getElementById('btnAddObat').addEventListener('click', addObatRow);

  document.addEventListener('change', function(e){
  if(!e.target.classList.contains('obat-select')) return;

  const row = e.target.closest('.obat-row');
  const opt = e.target.selectedOptions[0];

  const harga = Number(opt?.dataset?.harga || 0);
  const satuan = opt?.dataset?.satuan || '';

  row.querySelector('.obat-harga-raw').value = harga;
  row.querySelector('.obat-harga').value = rupiah(harga);
  row.querySelector('.obat-satuan').value = satuan;

  hitungSubtotal(row);
});
document.addEventListener('input', function(e){
  if(!e.target.classList.contains('obat-jumlah')) return;

  const row = e.target.closest('.obat-row');
  hitungSubtotal(row);
});
function hitungSubtotal(row){
  const qty = Number(row.querySelector('.obat-jumlah').value || 0);
  const harga = Number(row.querySelector('.obat-harga-raw').value || 0);

  const subtotal = qty * harga;

  row.querySelector('.obat-subtotal-raw').value = subtotal;
  row.querySelector('.obat-subtotal').value = rupiah(subtotal);

  hitungTotal();
}
function hitungTotal(){
  let total = 0;
  document.querySelectorAll('.obat-subtotal-raw').forEach(i => {
    total += Number(i.value || 0);
  });

  document.getElementById('totalHarga').innerText = rupiah(total);
}
document.addEventListener('change', function(e){
  if(!e.target.classList.contains('obat-select')) return;

  const row = e.target.closest('.obat-row');
  const opt = e.target.selectedOptions[0];

  const satuan = opt?.dataset?.satuan || '';
  row.querySelector('.obat-satuan').value = satuan;
});
document.getElementById('formPemeriksaan').addEventListener('submit', (e) => {
  const rows = document.querySelectorAll('#obatWrap .obat-row');

  for (const row of rows) {
    const obat = row.querySelector('.obat-select')?.value?.trim();
    if(!obat) continue; // baris kosong di-skip

    const satuanInput = row.querySelector('.obat-satuan');
    const jumlahInput = row.querySelector('.obat-jumlah') || row.querySelector('[name="jumlah[]"]');
    
    const satuan = satuanInput?.value?.trim();
    const jumlahRaw = jumlahInput?.value;

    const jumlah = Number(jumlahRaw || 0);
    if (!jumlahInput || !jumlahRaw || isNaN(jumlah) || jumlah <= 0) {
      e.preventDefault();

      Swal.fire({
        icon: 'warning',
        title: 'Data Obat Belum Lengkap',
        text: 'Jumlah obat wajib diisi.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#316BA1',
        heightAuto: false,  
        scrollbarPadding: false 
      }).then(() => {
        jumlahInput?.focus();
      });

      return;
    }

    if(!satuan){
      e.preventDefault();

      Swal.fire({
        icon: 'warning',
        title: 'Data Obat Belum Lengkap',
        text: 'Satuan wajib diisi.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#316BA1',
      }).then(() => {
        satuanInput?.focus();
      });

      return;
    }
  }
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.obat-select').forEach(sel => initObatSelect(sel));
});

document.addEventListener('DOMContentLoaded', () => {
  // init tomselect untuk select obat yang sudah ada
  function initObatSelect(selectEl){
    if (selectEl.tomselect) return; // biar ga double init

    new TomSelect(selectEl, {
      create: false,
      searchField: ['text'],
      placeholder: 'Cari obat...',
      allowEmptyOption: true,
  });
}
});
</script>
@endsection