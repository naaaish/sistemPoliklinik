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
                <div class="ap-label">Bidang</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <input type="text" name="bidang" id="bidang" value="{{ old('bidang') }}" placeholder="Bidang" readonly required>
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
                    <input type="text" name="nama_pasien" id="nama_pasien"
                        value="{{ old('nama_pasien') }}"
                        placeholder="Nama Lengkap Pasien" required>
                    <small class="ap-help" id="namaPasienHelp"></small>
                 </div>
            </div>

            <div class="ap-row">
                <div class="ap-label">Hubungan Keluarga</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <select name="hub_kel" id="hub_kel" class="ap-select" required>
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
                    <input type="date" name="tgl_lahir" id="tgl_lahir" value="{{ old('tgl_lahir') }}" required>
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
const nip = document.getElementById('nip');
const nipHelp = document.getElementById('nipHelp');

const namaPegawai = document.getElementById('nama_pegawai');
const bidang = document.getElementById('bidang');
const tglLahir = document.getElementById('tgl_lahir');

const tipePasien = document.getElementById('tipe_pasien');
const namaPasien = document.getElementById('nama_pasien');
const namaPasienHelp = document.getElementById('namaPasienHelp');

const hubKel = document.getElementById('hub_kel');

function isPensiunanBidang(){
  return (bidang.value || '').trim().toLowerCase() === 'pensiunan';
}

function lockYBS(){
  namaPasien.value = namaPegawai.value || '';
  namaPasien.readOnly = true;

  hubKel.value = 'YBS';
  hubKel.disabled = true;

  namaPasienHelp.textContent = '';
}

function unlockKeluarga(){
  namaPasien.readOnly = false;

  // hub_kel hanya pasangan/anak untuk keluarga
  if(hubKel.value === 'YBS') hubKel.value = 'pasangan';
  hubKel.disabled = false;

  namaPasienHelp.textContent = 'Isi nama pasien keluarga, pilih hubungan (pasangan/anak).';
  namaPasienHelp.style.color = '#5C6E9A';
}

function applyTanggalLahirByTipe(){
  const tipe = tipePasien.value;

  if(tipe === 'pegawai'){
    // auto isi kalau belum diisi
    if(!tglLahir.value && tglLahir.dataset.pegawaiDob){
      tglLahir.value = tglLahir.dataset.pegawaiDob;
    }
    // tetap bisa diedit
    tglLahir.readOnly = false;
    return;
  }

  // keluarga/pensiunan: admin isi manual
  tglLahir.readOnly = false;
  // kalau sebelumnya auto dari pegawai dan user pindah tipe, kosongkan biar input manual
  if(tglLahir.value === (tglLahir.dataset.pegawaiDob || '')){
    tglLahir.value = '';
  }
}


function applyRulesByTipe(){
  const tipe = tipePasien.value;

  // belum ada data pegawai dari NIP, jangan auto
  if(!namaPegawai.value){
    namaPasien.readOnly = false;
    hubKel.disabled = false;
    return;
  }

  if(tipe === 'pegawai'){
    // pegawai harus aktif (bukan pensiunan)
    if(isPensiunanBidang()){
      tipePasien.value = 'pensiunan';
      lockYBS();
      return;
    }
    lockYBS();
    return;
  }

  if(tipe === 'keluarga'){
    // keluarga hanya untuk pegawai aktif
    if(isPensiunanBidang()){
      tipePasien.value = 'pensiunan';
      lockYBS();
      return;
    }
    unlockKeluarga();
    return;
  }

  if(tipe === 'pensiunan'){
    // kalau bukan pensiunan, jangan boleh pilih pensiunan
    if(!isPensiunanBidang()){
      tipePasien.value = 'pegawai';
      lockYBS();
      return;
    }
    lockYBS();
    return;
  }
}

async function fetchPegawai(nipValue){
  nipHelp.textContent = '';
  nipHelp.style.color = '';

  if(!nipValue) return;

  try{
    const url = "{{ route('adminpoli.api.pegawai', '___') }}".replace('___', encodeURIComponent(nipValue));
    const res = await fetch(url);

    if(!res.ok){
      const j = await res.json().catch(()=>null);
      namaPegawai.value = '';
      bidang.value = '';
      tglLahir.value = '';
      nipHelp.textContent = j?.message || 'NIP tidak ditemukan';
      nipHelp.style.color = '#e74c3c';

      // reset pasien input
      namaPasien.value = '';
      namaPasien.readOnly = false;
      hubKel.disabled = false;
      return;
    }

    const json = await res.json();
    const d = json.data;

    namaPegawai.value = d.nama_pegawai || '';
    bidang.value = d.bidang || '';
    const pegawaiDob = (d.tgl_lahir || '').substring(0, 10);
    tglLahir.dataset.pegawaiDob = pegawaiDob;

    // aturan default setelah NIP ditemukan:
    // kalau pegawai bidang pensiunan → auto set tipe pensiunan
    if(isPensiunanBidang()){
      tipePasien.value = 'pensiunan';
    }else{
      tipePasien.value = 'pegawai';
    }

    applyRulesByTipe();

    nipHelp.textContent = 'Data pegawai ditemukan.';
    nipHelp.style.color = '#3bb54a';
  }catch(e){
    nipHelp.textContent = 'Gagal mengambil data pegawai.';
    nipHelp.style.color = '#e74c3c';
  }
}

nip.addEventListener('blur', () => fetchPegawai(nip.value.trim()));
tipePasien.addEventListener('change', applyRulesByTipe);

// sebelum submit, pastikan disabled field ikut terkirim
document.getElementById('formPendaftaran').addEventListener('submit', () => {
  hubKel.disabled = false;
});
</script>

@endsection
