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
                    <div class="ap-nip-wrap">
                      <input type="text" name="nip" id="nip" value="{{ old('nip') }}" placeholder="Ketik NIP / Nama..." autocomplete="off" required>
                      <div id="nipSuggest" class="ap-suggest" style="display:none;"></div>
                    </div>
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
                        <option value="-" selected>-</option>
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
                    <select name="petugas" id="petugas" class="ap-select" required>
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
const nipSuggestEl = document.getElementById('nipSuggest');
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
let searchTimer = null;
let lastQuery = '';
let suggestItems = [];
let activeIdx = -1;

function isSuggestOpen(){
  return nipSuggestEl && nipSuggestEl.style.display !== 'none' && suggestItems.length > 0;
}

function setActive(idx){
  if(!suggestItems.length) { activeIdx = -1; return; }

  // wrap around
  if(idx < 0) idx = suggestItems.length - 1;
  if(idx >= suggestItems.length) idx = 0;

  activeIdx = idx;

  // update class
  [...nipSuggestEl.querySelectorAll('.item')].forEach((el, i) => {
    el.classList.toggle('is-active', i === activeIdx);
  });

  // auto scroll into view
  const activeEl = nipSuggestEl.querySelector(`.item[data-idx="${activeIdx}"]`);
  if(activeEl) activeEl.scrollIntoView({ block: 'nearest' });
}

function closeSuggest(){
  nipSuggestEl.style.display = 'none';
  nipSuggestEl.innerHTML = '';
  suggestItems = [];
}

function renderSuggest(items){
  if(!items || items.length === 0){
    closeSuggest();
    return;
  }

  nipSuggestEl.innerHTML = items.map((it, idx) => {
    const nip = escapeHtml(it.nip);
    const nama = escapeHtml(it.nama_pegawai || '');
    const bagian = escapeHtml(it.bagian || '');
    return `
      <div class="item" data-idx="${idx}">
        <strong>${nip}</strong> — ${nama}
        <span class="meta">${bagian}</span>
      </div>
    `;
  }).join('');

  nipSuggestEl.style.display = 'block';
  suggestItems = items;
  setActive(0);
}

async function searchPegawai(q){
  const res = await fetch(`/adminpoli/api/pegawai/search?q=${encodeURIComponent(q)}`);
  const j = await res.json().catch(()=> null);

  console.log('SEARCH RESP:', j);

  if(!res.ok || !j) return [];

  if (Array.isArray(j)) return j;

  if (Array.isArray(j.data)) return j.data;

  return [];
}

// dipanggil kalau user sudah “fix” memilih pegawai
async function applyPegawaiSelected(p){
  closeSuggest();

  pegawaiData = p;
  keluargaData = [];
  resetNamaPasien();

  // set value input nip biar ikut terkirim
  nipEl.value = p.nip || '';

  namaPegEl.value = p.nama_pegawai || '';
  bagianEl.value = p.bagian ?? '';

  // AUTO pensiunan dari bagian (kode kamu tetap)
  const bagianVal = (bagianEl.value || '').trim().toLowerCase();
  const isPensiunan = bagianVal === 'pensiunan';

  if (isPensiunan) {
    tipeEl.value = 'pensiunan';
    tipeEl.disabled = false;
  } else {
    if (tipeEl.disabled) tipeEl.disabled = false;
    if (tipeEl.value === 'pensiunan') tipeEl.value = 'pegawai';
  }

  if (tipeEl.value) {
    onTipeChange().catch(()=>{});
  }

  // kalau kamu punya mode poliklinik
  checkPoliklinik();
}

// typing handler (debounce)
nipEl.addEventListener('input', () => {
  const q = (nipEl.value || '').trim();

  // reset kalau user ngubah nip
  pegawaiData = null;
  namaPegEl.value = '';
  bagianEl.value = '';
  resetNamaPasien();

  if (searchTimer) clearTimeout(searchTimer);

  searchTimer = setTimeout(async () => {
    // hindari request sama berulang
    if(q === lastQuery) return;
    lastQuery = q;

    const items = await searchPegawai(q);
    renderSuggest(items);
  }, 250);
});

nipEl.addEventListener('focus', async () => {
  const q = (nipEl.value || '').trim();

  // kalau kosong
  const items = await searchPegawai(q.length ? q : '1');
  renderSuggest(items);
});

// klik item suggestion
nipSuggestEl.addEventListener('click', (e) => {
  const item = e.target.closest('.item');
  if(!item) return;
  const idx = parseInt(item.dataset.idx, 10);
  const p = suggestItems[idx];
  if(!p) return;

  // langsung apply tanpa fetch lagi (lebih ringan)
  applyPegawaiSelected(p).catch(()=>{});
});

// kalau user klik di luar, tutup dropdown
document.addEventListener('click', (e) => {
  if(e.target === nipEl) return;
  if(nipSuggestEl.contains(e.target)) return;
  closeSuggest();
});

nipEl.addEventListener('keydown', async (e) => {
  // kalau dropdown terbuka, keyboard control
  if(isSuggestOpen()){
    if(e.key === 'ArrowDown'){
      e.preventDefault();
      setActive(activeIdx + 1);
      return;
    }

    if(e.key === 'ArrowUp'){
      e.preventDefault();
      setActive(activeIdx - 1);
      return;
    }

    if(e.key === 'Escape'){
      e.preventDefault();
      closeSuggest();
      return;
    }
  }

  // fallback Enter: kalau dropdown tidak terbuka, coba fetch exact NIP
  if(e.key === 'Enter'){
    e.preventDefault();
    const nip = (nipEl.value || '').trim();
    if(!nip) return;

    try{
      const p = await fetchPegawai(nip);
      await applyPegawaiSelected(p);
    }catch(err){
      alert(err.message);
    }
  }
});

nipSuggestEl.addEventListener('mousemove', (e) => {
  const item = e.target.closest('.item');
  if(!item) return;
  const idx = parseInt(item.dataset.idx, 10);
  if(!Number.isNaN(idx)) setActive(idx);
});

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
  return j.data; // { nip, nama_pegawai, bagian, tgl_lahir }
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

  bagianEl.value = p.bagian ?? p.bagian ?? '';

  // IMPORTANT: di sini STOP. Jangan isi nama_pasien dulu.
  // === AUTO pensiunan dari bagian ===
  const bagianVal = (bagianEl.value || '').trim().toLowerCase();
  const isPensiunan = bagianVal === 'pensiunan';

  // kalau pensiunan -> paksa tipe_pasien = pensiunan
  if (isPensiunan) {
    tipeEl.value = 'pensiunan';
    tipeEl.disabled = false;
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

tipeEl.addEventListener('change', () => {
  onTipeChange().catch(err => {
    resetNamaPasien();
    alert(err.message);
  });
});

namaPasSelect.addEventListener('change', applySelectedPasien);

// function setPoliklinikMode(isPoli) {
//   const namaPasienEl = document.getElementById('nama_pasien');
//   const hubKelEl = document.getElementById('hub_kel');
//   const tglLahirEl = document.getElementById('tgl_lahir');

//   if (!namaPasienEl || !hubKelEl || !tglLahirEl) return;

//   if (isPoli) {
//     // set value ke "-"
//     namaPasienEl.value = '-';
//     hubKelEl.value = 'YBS';
//     tglLahirEl.value = '2000-01-01'; // dummy aman
//     // tglLahirEl.classList.add('hidden-field');
//     // kunci field
//     namaPasienEl.readOnly = true;
//     hubKelEl.readOnly = true;
//     tglLahirEl.readOnly = true;
//   } else {
//     namaPasienEl.disabled = false;
//     hubKelEl.disabled = false;
//     tglLahirEl.readOnly = false;
//   }
// }

function setPoliklinikMode(isPoli) {
  const petugasEl = document.getElementById('petugas');

  if (isPoli) {
    // AUTO isi atas
    nipEl.value = '001';
    namaPegEl.value = 'Poliklinik';
    bagianEl.value = 'Poliklinik';

    // AUTO set tipe
    tipeEl.value = 'pegawai';
    tipeEl.disabled = true; // biar ga bisa diganti

    // AUTO isi pasien
    namaPasSelect.innerHTML = `<option value="-" selected data-hub="YBS" data-tgl="" data-idkel="">-</option>`;
    namaPasSelect.value = '-';
    hubEl.value = 'YBS';
    idKelEl.value = '';

    // tgl lahir: kosongin aja
    tglEl.value = '';
    tglEl.readOnly = true;

    // kunci UI tanpa disable (biar tetap terkirim)
    namaPasSelect.classList.add('is-locked');
    hubEl.classList.add('is-locked');

    // AUTO pilih pemeriksa “Sofia”
    if (petugasEl) {
      const opt = [...petugasEl.options].find(o =>
        (o.value || '').startsWith('pemeriksa:')
      );
      if (opt) petugasEl.value = opt.value;
    }

  } else {
    tipeEl.disabled = false;
    namaPasSelect.classList.remove('is-locked');
    hubEl.classList.remove('is-locked');
    tglEl.readOnly = false;
  }
}

function checkPoliklinik() {
  const bagianEl = document.getElementById('bagian');
  const nipEl = document.getElementById('nip');
  const namaPegawaiEl = document.getElementById('nama_pegawai');

  const bagian = (bagianEl?.value || '').toLowerCase().trim();
  const nip = (nipEl?.value || '').trim();
  const namaPegawai = (namaPegawaiEl?.value || '').toLowerCase().trim();

  const isPoli = (bagian === 'poliklinik') || (nip === '001') || (namaPegawai === 'poliklinik');
  setPoliklinikMode(isPoli);
}

// panggil saat halaman load & setelah autofill nip
checkPoliklinik();

document.getElementById('formPendaftaran').addEventListener('submit', () => {
  hubEl.disabled = false;
  tipeEl.disabled = false;

  const namaPasienEl = document.getElementById('nama_pasien');
  if (namaPasienEl) namaPasienEl.disabled = false;
});


</script>

@endsection
