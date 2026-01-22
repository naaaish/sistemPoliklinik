@extends('layouts.adminpoli')

@section('title', 'Edit Hasil Pemeriksaan Pasien')

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


    <form id="formPemeriksaan" method="POST" action="{{ route('adminpoli.pemeriksaan.update', $pendaftaran->id_pendaftaran) }}">
      @csrf
      @method('PUT')

      {{-- ===== DATA PEMERIKSAAN ===== --}}  
      <div style="color:#316BA1;font-size:19px;margin:18px 0 10px;">
        Data Pemeriksaan Kesehatan
      </div>

      <div class="ap-vitals-grid">
        <div class="ap-vital-item">
          <div class="ap-vital-label">Sistol</div>
          <input class="ap-vital-input"
            name="sistol"
            value="{{ old('sistol', $hasil->sistol) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Diastol</div>
          <input class="ap-vital-input"
            name="diastol"
            value="{{ old('diastol', $hasil->diastol) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Denyut Nadi</div>
          <input class="ap-vital-input"
            name="nadi"
            value="{{ old('nadi', $hasil->nadi) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Puasa</div>
          <input class="ap-vital-input"
            name="gd_puasa"
            value="{{ old('gd_puasa', $hasil->gd_puasa) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>2 jam PP</div>
          <input class="ap-vital-input"
            name="gd_duajam"
            value="{{ old('gd_duajam', $hasil->gd_duajam) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Sewaktu</div>
          <input class="ap-vital-input"
            name="gd_sewaktu"
            value="{{ old('gd_sewaktu', $hasil->gd_sewaktu) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Asam Urat</div>
          <input class="ap-vital-input"
            name="asam_urat"
            value="{{ old('asam_urat', $hasil->asam_urat) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Cholesterol</div>
          <input class="ap-vital-input"
            name="chol"
            value="{{ old('chol', $hasil->chol) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Trigliseride</div>
          <input class="ap-vital-input"
            name="tg"
            value="{{ old('tg', $hasil->tg) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Suhu</div>
          <input class="ap-vital-input"
            name="suhu"
            value="{{ old('suhu', $hasil->suhu) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Berat Badan</div>
          <input class="ap-vital-input"
            name="berat"
            value="{{ old('berat', $hasil->berat) }}"
            placeholder="-"
            readonly>
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Tinggi Badan</div>
          <input class="ap-vital-input"
            name="tinggi"
            value="{{ old('tinggi', $hasil->tinggi) }}"
            placeholder="-"
            readonly>
        </div>
      </div>
      {{-- ===== DIAGNOSA ===== --}}
      <div class="ap-form-row">
        <div class="ap-form-label">Penyakit</div>
        <div class="ap-form-sep">:</div>
        <div class="ap-form-value">
          <div class="hasil-list">
            @if(count($penyakitTerpilih))
              <ul>
                @foreach($penyakitTerpilih as $p)
                  <li>{{ $p }}</li>
                @endforeach
              </ul>
            @else
              -
            @endif
          </div>
        </div>
      </div>

      <div class="ap-form-row">
        <div class="ap-form-label">Diagnosa K3</div>
        <div class="ap-form-sep">:</div>
        <div class="ap-form-value">
          <div class="hasil-list">
            @if(count($diagnosaK3Terpilih ?? []))
              <ul>
                @foreach($diagnosaK3Terpilih as $d)
                  <li>{{ $d }}</li>
                @endforeach
              </ul>
            @else
              -
            @endif
          </div>
        </div>
      </div>

      <div class="ap-form-row">
        <div class="ap-form-label">Saran</div>
        <div class="ap-form-sep">:</div>
        <div class="ap-form-value">
          <div class="hasil-list">
            @if(count($saranTerpilih ?? []))
              <ul>
                @foreach($saranTerpilih as $s)
                  <li>{{ $s }}</li>
                @endforeach
              </ul>
            @else
              -
            @endif
          </div>
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

      <div id="obatWrap">
        @if(isset($detailResep) && $detailResep->count())
          @foreach($detailResep as $dr)
            <div class="obat-row">
              <select class="obat-select" name="obat_id[]">
                <option value="">-- pilih obat (boleh kosong) --</option>
                @foreach($obat as $o)
                  <option value="{{ $o->id_obat }}"
                          data-harga="{{ $o->harga }}"
                          data-satuan="{{ $o->satuan ?? '' }}"
                          {{ $o->id_obat == $dr->id_obat ? 'selected' : '' }}>
                    {{ $o->nama_obat }}
                  </option>
                @endforeach
              </select>

              <input class="obat-jumlah" type="number" min="1" name="jumlah[]" value="{{ (int)$dr->jumlah }}">
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
document.getElementById('formPemeriksaan').addEventListener('submit', function (e) {
    let valid = true;
    let firstInvalidSatuan = null;

    // semua row obat
    document.querySelectorAll('#obatWrap .obat-row').forEach((row, index) => {
        const obatSelect = row.querySelector('select[name="obat_id[]"]');
        const satuanInput = row.querySelector('input[name="satuan[]"]');

        if (!obatSelect || !satuanInput) return;

        const obatVal = obatSelect.value;
        const satuanVal = satuanInput.value.trim();

        if (obatVal && satuanVal === '') {
            valid = false;
            if (!firstInvalidSatuan) {
                firstInvalidSatuan = satuanInput;
            }
        }
    });

    if (!valid) {
        e.preventDefault(); // STOP SUBMIT

        Swal.fire({
            icon: 'warning',
            title: 'Satuan belum diisi',
            text: 'Jika obat dipilih, satuan wajib diisi.',
            confirmButtonText: 'OK'
        }).then(() => {
            if (firstInvalidSatuan) {
                firstInvalidSatuan.focus();
            }
        });
    }
});
</script>

<script>
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
        }).then(() => {
          obatDuplicateAlertOpen = false;
          // fokus balik ke select biar user enak ganti
          setTimeout(() => e.target.focus(), 50);
        });
      }

      return;
    }
  });
</script>

@endsection