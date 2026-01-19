@extends('layouts.adminpoli')

@section('title', 'Input Hasil Pemeriksaan Pasien')

@php
  function renderList($items){
    if(empty($items) || count($items) === 0){
      return '<div class="info-item">-</div>';
    }

    $html = '<div class="info-list">';
    foreach($items as $it){
      $html .= '<div class="info-item">'.e($it).'</div>';
    }
    $html .= '</div>';

    return $html;
  }
@endphp

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

    <form method="POST" action="{{ route('adminpoli.pemeriksaan.update', $pendaftaran->id_pendaftaran) }}" id="formPemeriksaan">
      @csrf
      @method('PUT')

      {{-- ===== INFO PASIEN ===== --}}

      {{-- ===== DATA PEMERIKSAAN ===== --}}  
    <div style="color:#316BA1;font-size:19px;margin:18px 0 10px;">
        Data Pemeriksaan Kesehatan
      </div>

      <div class="ap-vitals-grid">
        {{-- BARIS 1 --}}
        <div class="ap-vital-item">
          <div class="ap-vital-label">Sistol</div>
                  <input class="ap-vital-input" name="sistol"
              value="{{ old('sistol', $hasil->sistol) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Diastol</div>
          <input class="ap-vital-input" name="diastol"
              value="{{ old('diastol', $hasil->diastol) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Denyut Nadi</div>
          <input class="ap-vital-input" name="nadi"
              value="{{ old('nadi', $hasil->nadi) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Puasa</div>
          <input class="ap-vital-input" name="gd_puasa"
              value="{{ old('gd_puasa', $hasil->gd_puasa) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>2 jam PP</div>
          <input class="ap-vital-input" name="gd_duajam"
              value="{{ old('gd_duajam', $hasil->gd_duajam) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Sewaktu</div>
          <input class="ap-vital-input" name="gd_sewaktu"
              value="{{ old('gd_sewaktu', $hasil->gd_sewaktu) }}"
              readonly>
        </div>

        {{-- BARIS 2 --}}
        <div class="ap-vital-item">
          <div class="ap-vital-label">Asam Urat</div>
          <input class="ap-vital-input" name="asam_urat"
              value="{{ old('asam_urat', $hasil->asam_urat) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Cholesterol</div>
          <input class="ap-vital-input" name="chol"
              value="{{ old('chol', $hasil->chol) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Trigliseride</div>
          <input class="ap-vital-input" name="tg"
              value="{{ old('tg', $hasil->tg) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Suhu</div>
          <input class="ap-vital-input" name="suhu"
              value="{{ old('suhu', $hasil->suhu) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Berat Badan</div>
          <input class="ap-vital-input" name="berat"
              value="{{ old('berat', $hasil->berat) }}"
              readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Tinggi Badan</div>
          <input class="ap-vital-input" name="tinggi"
              value="{{ old('tinggi', $hasil->tinggi) }}"
              readonly>
        </div>
      </div>
      {{-- ===== DIAGNOSA ===== --}}
      <div class="ap-form-row">
        <div class="ap-form-label">Penyakit</div>
        <div class="ap-form-sep">:</div>
        <div class="ap-form-value">
          @if(count($penyakitTerpilih ?? []) === 0)
            <div class="ap-readonly-pill">-</div>
          @else
            @foreach(($penyakitTerpilih ?? []) as $p)
              <div class="ap-readonly-pill">{{ $p }}</div>
            @endforeach
          @endif
        </div>
      </div>

      <div class="ap-form-row">
        <div class="ap-form-label">Diagnosa K3</div>
        <div class="ap-form-sep">:</div>
        <div class="ap-form-value">
          @if(count($diagnosaK3Terpilih ?? []) === 0)
            <div class="ap-readonly-pill">-</div>
          @else
            @foreach(($diagnosaK3Terpilih ?? []) as $d)
              <div class="ap-readonly-pill">{{ $d }}</div>
            @endforeach
          @endif
        </div>
      </div>

      <div class="ap-form-row">
        <div class="ap-form-label">Saran</div>
        <div class="ap-form-sep">:</div>
        <div class="ap-form-value">
          @if(count($saranTerpilih ?? []) === 0)
            <div class="ap-readonly-pill">-</div>
          @else
            @foreach(($saranTerpilih ?? []) as $s)
              <div class="ap-readonly-pill">{{ $s }}</div>
            @endforeach
          @endif
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

    filterSaranByDiagnosa(sel.value);

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

    const select = templateRow.querySelector('.obat-select');
    const qtyEl = templateRow.querySelector('[name="jumlah[]"]');
    const satuanEl = templateRow.querySelector('[name="satuan[]"]');
    const hargaEl = templateRow.querySelector('[name="harga_satuan[]"]');

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

    obatWrap.appendChild(templateRow);
    hitungTotal();
  }

  document.getElementById('btnAddObat').addEventListener('click', addObatRow);

  // NOTE: kalau mau awalnya ada 1 row obat, aktifin ini:
  // addObatRow();

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
  row.querySelector('.obat-satuan').value = satuan;   // <<< INI

  // kalau kamu juga isi harga raw/tampilan, biarkan seperti biasa
});
document.getElementById('formPemeriksaan').addEventListener('submit', (e) => {
  const rows = document.querySelectorAll('#obatWrap .obat-row');

  for (const row of rows) {
    const obat = row.querySelector('.obat-select')?.value?.trim();
    if(!obat) continue; // baris kosong di-skip

    const satuanInput = row.querySelector('.obat-satuan');
    const satuan = satuanInput?.value?.trim();

    if(!satuan){
      e.preventDefault();

      Swal.fire({
        icon: 'warning',
        title: 'Data Obat Belum Lengkap',
        text: 'Satuan wajib diisi jika obat dipilih.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#316BA1',
      }).then(() => {
        satuanInput?.focus();
      });

      return;
    }
  }
});

</script>
@endsection