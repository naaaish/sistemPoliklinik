@extends('layouts.adminpoli')
@section('title', 'Edit Hasil Pemeriksaan Pasien')

@section('content')
<div class="ap-page">

  <div class="ap-topbar">
    <a href="{{ route('adminpoli.pemeriksaan.show', $pendaftaran->id_pendaftaran) }}" class="ap-back-inline">
      <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="kembali">
    </a>
    <h1 class="ap-title">Edit Hasil Pemeriksaan Pasien</h1>
  </div>

  <div class="ap-card-form">
    <div class="ap-form-head">
      <div class="ap-form-head__title">Formulir Hasil Pemeriksaan</div>
      <div class="ap-form-head__sub">Pasien Poliklinik PT PLN Indonesia Power UBP Mrica</div>
    </div>

    <form method="POST" action="{{ route('adminpoli.pemeriksaan.update', $pendaftaran->id_pendaftaran) }}" id="formPemeriksaan">
      @csrf
      @method('PUT')

      <div style="color:#316BA1;font-size:19px;margin:18px 0 10px;">Data Pemeriksaan Kesehatan</div>

      <div class="ap-vitals-grid">
        <div class="ap-vital-item">
          <div class="ap-vital-label">Sistol</div>
          <input class="ap-vital-input" name="sistol" value="{{ old('sistol', $hasil->sistol) }}">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Diastol</div>
          <input class="ap-vital-input" name="diastol" value="{{ old('diastol', $hasil->diastol) }}">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Denyut Nadi</div>
          <input class="ap-vital-input" name="nadi" value="{{ old('nadi', $hasil->nadi) }}">
        </div>

        <div class="ap-vital-item">
          <div class="ap-vital-label">Gula Darah<br>Puasa</div>
          <input class="ap-vital-input" name="gula_puasa" value="{{ old('gula_puasa', $hasil->gd_puasa ?? $hasil->gula_puasa) }}">
        </div>

        {{-- lanjutkan field lain sesuai create kamu --}}
      </div>

      {{-- =========================
           OBAT & HARGA (PREFILL)
           ========================= --}}
      <div style="color:#316BA1;font-size:19px;margin:18px 0 10px;">Obat & Harga</div>

      <div class="obat-header">
        <div>Nama Obat</div><div>Jumlah</div><div>Satuan</div><div>Harga Satuan</div><div>Subtotal</div><div></div>
      </div>

      <div id="obatWrap">
        @php
          $rows = old('obat_id') ? count(old('obat_id')) : ($detailResep->count() ?: 1);
        @endphp

        @for($i=0; $i<$rows; $i++)
          @php
            $oldObat   = old("obat_id.$i", $detailResep[$i]->id_obat ?? '');
            $oldJumlah = old("jumlah.$i", $detailResep[$i]->jumlah ?? 1);
            $oldSatuan = old("satuan.$i", $detailResep[$i]->satuan ?? '');
            $oldSub    = old("subtotal.$i", $detailResep[$i]->subtotal ?? 0);
          @endphp

          <div class="obat-row">
            <select class="obat-select" name="obat_id[]">
              <option value="">-- pilih obat (boleh kosong) --</option>
              @foreach($obat as $o)
                <option value="{{ $o->id_obat }}"
                  data-harga="{{ (int)($o->harga ?? 0) }}"
                  data-satuan="{{ $o->satuan ?? '' }}"
                  @selected($oldObat == $o->id_obat)
                >
                  {{ $o->nama_obat }}
                </option>
              @endforeach
            </select>

            <input class="obat-jumlah" name="jumlah[]" value="{{ $oldJumlah }}">
            <input class="obat-satuan" name="satuan[]" value="{{ $oldSatuan }}">

            <input class="obat-harga" value="">
            <input type="hidden" class="obat-harga-raw" name="harga_satuan[]" value="0">

            <input class="obat-subtotal" value="">
            <input type="hidden" class="obat-subtotal-raw" name="subtotal_raw[]" value="{{ (int)$oldSub }}">

            <button type="button" class="obat-hapus">Hapus</button>
          </div>
        @endfor
      </div>

      <button type="button" id="btnAddObat" class="ap-btn-outline">Tambah Obat/Alkes</button>

      <div class="ap-total-line">
        <div class="ap-total-label">Total :</div>
        <div class="ap-total-val" id="totalHarga">Rp0</div>
      </div>

      <button type="submit" class="ap-submit">Update</button>
    </form>
  </div>
</div>
@endsection
<script>
document.querySelectorAll('#obatWrap .obat-row').forEach(row => {
  const sel = row.querySelector('.obat-select');
  const opt = sel?.selectedOptions?.[0];

  const harga = Number(opt?.dataset?.harga || 0);
  const satuan = opt?.dataset?.satuan || row.querySelector('.obat-satuan').value || '';

  row.querySelector('.obat-harga-raw').value = harga;
  row.querySelector('.obat-harga').value = rupiah(harga);

  row.querySelector('.obat-satuan').value = satuan;

  hitungSubtotal(row);
});
hitungTotal();

</script>