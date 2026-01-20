@extends('layouts.adminpoli')

@section('title', 'Input Pendaftaran Pasien')

@section('content')
<div class="ap-page">

    <div class="ap-topbar">
        <a href="{{ route('adminpoli.dashboard') }}" class="ap-back-inline"><img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="kembali"></a>
        <h1 class="ap-title">Input Pendaftaran Pasien</h1>
    </div>

    <div class="ap-card-form">
        <div class="ap-form-head">
            <div class="ap-form-head__title">Pendaftaran</div>
            <div class="ap-form-head__sub">Pasien Poliklinik PT PLN Indonesia Power UBP Mrica</div>
        </div>

        <form method="POST" action="{{ route('adminpoli.pendaftaran.store') }}" id="formPendaftaran">
            @csrf

            <div class="ap-row">
                <div class="ap-label">Tanggal Periksa</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required>
                </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">NIP Pegawai</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <input type="text" name="nip" id="nip" value="{{ old('nip') }}" placeholder="NIP Pegawai" required>
                    <small class="ap-help" id="nipHelp"></small>
                </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">Nama Lengkap</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <input type="text" name="nama_pegawai" id="nama_pegawai" value="{{ old('nama_pegawai') }}"
                           placeholder="Nama Lengkap Pegawai" readonly required>
                </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">Bagian</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <input type="text" name="bagian" id="bagian" value="{{ old('bagian') }}" placeholder="Bagian" readonly required>
                </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">Tipe Pasien</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <select name="tipe_pasien" id="tipe_pasien" class="ap-select" required>
                        <option value="pegawai">Pegawai</option>
                        <option value="keluarga">Keluarga</option>
                        <option value="pensiunan">Pensiunan</option>
                    </select>
                </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">Nama Pasien</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <select name="nama_pasien" id="nama_pasien" class="ap-select" required>
                        <option value="">-- pilih nama pasien --</option>
                    </select>
                    <small class="ap-help" id="namaPasienHelp"></small>
                 </div>
            </div>

            <input type="hidden" name="id_keluarga" id="id_keluarga">

            <div class="ap-row">
                <div class="ap-label">Hubungan Keluarga</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <select name="hub_kel" id="hub_kel" class="ap-select" required disabled>
                        <option value="YBS">YBS</option>
                        <option value="Pasangan">Pasangan</option>
                        <option value="Anak">Anak</option>
                    </select>
                </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">Tanggal Lahir</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <input type="date" name="tgl_lahir" id="tgl_lahir" value="{{ old('tgl_lahir') }}" readonly required>
                </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">Jenis Pemeriksaan</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <select name="jenis_pemeriksaan" class="ap-select" required>
                        <option value="cek_kesehatan">Cek Kesehatan</option>
                        <option value="berobat">Berobat</option>
                    </select>
                </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">Dokter/Pemeriksa</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <select name="petugas" class="ap-select" required>
                        <option value="">-- pilih --</option>
                        <optgroup label="Dokter">
                            @foreach($dokter as $d)
                                <option value="dokter:{{ $d->id_dokter }}">{{ $d->nama }} ({{ $d->jenis_dokter }})</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Pemeriksa">
                            @foreach($pemeriksa as $p)
                                <option value="pemeriksa:{{ $p->id_pemeriksa }}">{{ $p->nama_pemeriksa }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>

            <div class="ap-row ap-row--textarea">
                <div class="ap-label">Keluhan</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <textarea name="keluhan" rows="4" placeholder="Keluhan Pasien">{{ old('keluhan') }}</textarea>
                </div>
            </div>

            <button class="ap-register" type="submit">Register</button>
        </form>
    </div>

    <footer class="ap-footer">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </footer>
</div>
<script>
const nipEl = document.getElementById('nip');
const tipeEl = document.getElementById('tipe_pasien');

const namaPegEl = document.getElementById('nama_pegawai');
const bagianEl  = document.getElementById('bagian');

const namaPasSelect = document.getElementById('nama_pasien');
const hubEl = document.getElementById('hub_kel');
const tglEl = document.getElementById('tgl_lahir');
const idKelEl = document.getElementById('id_keluarga');

// cache
let pegawaiData = null;
let keluargaData = [];

// === helpers ===
function resetNamaPasien(){
  namaPasSelect.innerHTML = `<option value="">-- pilih pasien --</option>`;
  hubEl.value = '';
  tglEl.value = '';
  idKelEl.value = '';
}

function applySelectedPasien(){
  const opt = namaPasSelect.selectedOptions[0];
  if(!opt || !opt.value){
    hubEl.value = '';
    tglEl.value = '';
    idKelEl.value = '';
    return;
  }

  hubEl.value = opt.dataset.hub || '';
  tglEl.value = opt.dataset.tgl || '';

  // kalau pilih pegawai => id_keluarga kosong
  idKelEl.value = opt.dataset.idkel || '';
}

async function fetchPegawai(nip){
  const res = await fetch(`/adminpoli/api/pegawai/${encodeURIComponent(nip)}`);
  const j = await res.json().catch(()=> ({}));
  if(!res.ok || !j.ok) throw new Error(j.message || 'NIP tidak ditemukan');
  return j.data; // { nip, nama_pegawai, bidang/bagian, tgl_lahir }
}

async function fetchKeluarga(nip){
  const res = await fetch(`/adminpoli/api/pegawai/${encodeURIComponent(nip)}/keluarga`);
  const j = await res.json().catch(()=> ({}));
  if(!res.ok || !j.ok) throw new Error(j.message || 'Gagal ambil keluarga');
  return j.data; // [{id_keluarga,nama,hubungan_keluarga,tgl_lahir,umur,covered}]
}

// === Step 1: NIP -> isi nama pegawai + bagian (STOP) ===
async function onNipDone(){
  const nip = (nipEl.value || '').trim();
  pegawaiData = null;
  keluargaData = [];
  resetNamaPasien();

  if(!nip) return;

  const p = await fetchPegawai(nip);
  pegawaiData = p;

  namaPegEl.value = p.nama_pegawai || '';
  // kamu nyebutnya BAGIAN (bukan bidang)
  bagianEl.value = p.bagian ?? p.bidang ?? '';

  // IMPORTANT: di sini STOP. Jangan isi nama_pasien dulu.
  // === AUTO pensiunan dari bagian ===
  const bagianVal = (bagianEl.value || '').trim().toLowerCase();
  const isPensiunan = bagianVal === 'pensiunan';

  // kalau pensiunan -> paksa tipe_pasien = pensiunan
  if (isPensiunan) {
    tipeEl.value = 'pensiunan';
    tipeEl.disabled = true;       // optional: biar ga bisa ganti
  } else {
    if (tipeEl.disabled) tipeEl.disabled = false;
    // kalau sebelumnya kepaksa pensiunan, balikin ke pegawai default
    if (tipeEl.value === 'pensiunan') tipeEl.value = 'pegawai';
  }
  if (tipeEl.value) {
    onTipeChange().catch(()=>{});
  }
}

// === Step 2: pilih tipe -> populate dropdown nama pasien ===
async function onTipeChange(){
  resetNamaPasien();

  if(!pegawaiData){
    // belum isi NIP / gagal fetch pegawai
    return;
  }

  const tipe = tipeEl.value;

  // 2A) pegawai: dropdown hanya pegawai, auto select
  if(tipe === 'pegawai'){
    namaPasSelect.innerHTML =
      `<option value="${escapeHtml(pegawaiData.nama_pegawai)}"
        data-hub="YBS"
        data-tgl="${(pegawaiData.tgl_lahir || '').substring(0,10)}"
        data-idkel="">
        ${escapeHtml(pegawaiData.nama_pegawai)}
      </option>`;
    namaPasSelect.selectedIndex = 0;
    applySelectedPasien();
    return;
  }

  // 2B) keluarga / pensiunan: butuh list keluarga (pasangan + anak covered)
  keluargaData = await fetchKeluarga(pegawaiData.nip);

  let opts = `<option value="">-- pilih pasien --</option>`;

  // kalau pensiunan: gabung (pegawai + keluarga)
  if(tipe === 'pensiunan'){
    opts +=
      `<option value="${escapeHtml(pegawaiData.nama_pegawai)}"
        data-hub="YBS"
        data-tgl="${(pegawaiData.tgl_lahir || '').substring(0,10)}"
        data-idkel="">
        ${escapeHtml(pegawaiData.nama_pegawai)} (Pegawai)
      </option>`;
  }

  keluargaData.forEach(k => {
    // pasangan selalu tampil
    if(k.hubungan_keluarga === 'pasangan'){
      opts +=
        `<option value="${escapeHtml(k.nama)}"
          data-hub="Pasangan"
          data-tgl="${(k.tgl_lahir || '').substring(0,10)}"
          data-idkel="${k.id_keluarga}">
          ${escapeHtml(k.nama)} (Pasangan)
        </option>`;
      return;
    }

    // anak: hanya yang covered
    if(k.hubungan_keluarga === 'anak' && k.covered){
      opts +=
        `<option value="${escapeHtml(k.nama)}"
          data-hub="Anak"
          data-tgl="${(k.tgl_lahir || '').substring(0,10)}"
          data-idkel="${k.id_keluarga}">
          ${escapeHtml(k.nama)} (Anak, ${k.umur} th)
        </option>`;
    }
  });

  namaPasSelect.innerHTML = opts;

  // kalau tipe keluarga: auto select option pertama selain placeholder (biar enak)
  if(tipe === 'keluarga' && namaPasSelect.options.length > 1){
    namaPasSelect.selectedIndex = 1;
    applySelectedPasien();
  }
}

// anti-XSS kecil
function escapeHtml(str){
  return (str || '').replace(/[&<>"']/g, (m) => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
  }[m]));
}

// listeners
nipEl.addEventListener('blur', () => {
  onNipDone().catch(err => {
    // kalau gagal, kosongin pegawai/bagian biar jelas
    namaPegEl.value = '';
    bagianEl.value = '';
    resetNamaPasien();
    alert(err.message);
  });
});

tipeEl.addEventListener('change', () => {
  onTipeChange().catch(err => {
    resetNamaPasien();
    alert(err.message);
  });
});

namaPasSelect.addEventListener('change', applySelectedPasien);

// before submit: enable hub_kel biar terkirim
document.getElementById('formPendaftaran').addEventListener('submit', () => {
  hubEl.disabled = false;
});
</script>

@endsection
