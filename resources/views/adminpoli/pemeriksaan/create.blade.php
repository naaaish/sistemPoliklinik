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
              <option value="{{ $d->id_diagnosa_k3 }}">{{ $d->nama_penyakit }}</option>
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
                <option value="{{ $s->id_saran }}">{{ $s->isi }}</option>
            @endforeach
          </select>

          <button type="button" id="btnAddSaran" class="ap-btn-small">Tambah Saran</button>

          <div id="chipSaran" style="margin-top:10px;"></div>
          <div id="hiddenSaran"></div>
        </div>
      </div>

      {{-- ===== OBAT & HARGA ===== --}}
      <div style="color:#316BA1;font-size:19px;margin:22px 0 10px;">Obat & Harga</div>

      {{-- TEMPLATE ROW OBAT (hidden) --}}
      <div id="obatTemplate" style="display:none;">
        <div class="obat-row" style="display:flex;gap:10px;align-items:center;margin:10px 0;flex-wrap:wrap;">
          <select name="obat_id[]" class="ap-select obat-select" style="min-width:180px;">
            <option value="">-- pilih obat (boleh kosong) --</option>
            @foreach($obat as $o)
              <option value="{{ $o->id_obat }}"
                data-harga="{{ $o->harga ?? 0 }}"
                data-satuan="{{ $o->satuan ?? '' }}">
                {{ $o->nama_obat }}
              </option>
            @endforeach
          </select>

          <input name="jumlah[]" type="number" min="1" placeholder="Jumlah" style="width:90px;">
          <input name="satuan[]" type="text" placeholder="Satuan" style="width:120px;">
          <input name="harga_satuan[]" type="number" min="0" placeholder="Harga Satuan" style="width:140px;">
          <input class="subtotal" type="number" placeholder="Subtotal" style="width:140px;" readonly>

          <button type="button" class="btnDel"
            style="border:none;background:#ffe7e7;color:#b30000;padding:8px 10px;border-radius:8px;cursor:pointer;">
            Hapus
          </button>
        </div>
      </div>

      <div id="obatWrap"></div>

      <button type="button" id="btnAddObat" class="ap-btn-small">Tambah Obat/Alkes</button>

      <div style="text-align:right;margin-top:10px;font-weight:600;color:#787676;">
        Total : <span id="totalText">Rp0</span>
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
      'display:inline-flex;align-items:center;gap:8px;background:#eef3ff;border:1px solid #c7d7f5;' +
      'color:#316BA1;padding:6px 10px;border-radius:14px;margin:4px 6px 0 0;';
    chip.innerHTML =
      `<span>${label}</span>` +
      `<button type="button" style="border:none;background:transparent;cursor:pointer;font-weight:700;color:#316BA1;">×</button>`;

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
</script>
@endsection