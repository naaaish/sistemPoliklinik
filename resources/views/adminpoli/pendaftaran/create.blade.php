@extends('layouts.adminpoli')

@section('title', 'Input Pendaftaran Pasien')

@section('content')
<div class="ap-page">

    <div class="ap-topbar">
        <a href="{{ route('adminpoli.dashboard') }}" class="ap-back">←</a>
        <h1 class="ap-title">Input Pendaftaran Pasien</h1>
    </div>

    @if($errors->any())
        <div class="ap-alert ap-alert--error">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                <div class="ap-label">Nama Pasien</div>
                <div class="ap-colon">:</div>
                <div class="ap-input">
                    <div class="ap-split">
                        <select id="mode_pasien" class="ap-select">
                            <option value="ybs">YBS (Pegawai)</option>
                            <option value="manual">Input Manual (Keluarga)</option>
                        </select>
                        <input type="text" name="nama_pasien" id="nama_pasien" value="{{ old('nama_pasien') }}"
                               placeholder="Nama Lengkap Pasien" required>
                    </div>
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
const nip = document.getElementById('nip');
const nipHelp = document.getElementById('nipHelp');
const namaPegawai = document.getElementById('nama_pegawai');
const bidang = document.getElementById('bidang');
const tglLahir = document.getElementById('tgl_lahir');

const modePasien = document.getElementById('mode_pasien');
const namaPasien = document.getElementById('nama_pasien');

const tipePasien = document.getElementById('tipe_pasien');
const hubKel = document.getElementById('hub_kel');

function applyRules(){
  const isPensiunan = (bidang.value || '').trim().toLowerCase() === 'pensiunan';
  const isYbs = (namaPegawai.value || '').trim().toLowerCase() === (namaPasien.value || '').trim().toLowerCase();

  if(isPensiunan){
    tipePasien.value = 'pensiunan';
  } else if(isYbs){
    tipePasien.value = 'pegawai';
  } else {
    tipePasien.value = 'keluarga';
  }

  if(isYbs || isPensiunan){
    hubKel.value = 'YBS';
    hubKel.disabled = true;
  }else{
    hubKel.disabled = false;
    if(hubKel.value === 'YBS') hubKel.value = 'Pasangan';
  }

  tipePasien.disabled = true; // kunci
}

function setYBS(){
  if(!namaPegawai.value) return;
  namaPasien.value = namaPegawai.value;
  applyRules();
}

function setManual(){
  namaPasien.value = '';
  applyRules();
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
      return;
    }

    const json = await res.json();
    const d = json.data;

    namaPegawai.value = d.nama_pegawai || '';
    bidang.value = d.bidang || '';
    tglLahir.value = (d.tgl_lahir || '').substring(0, 10);

    if(modePasien.value === 'ybs' || !namaPasien.value){
      setYBS();
    }else{
      applyRules();
    }

    nipHelp.textContent = 'Data pegawai ditemukan.';
    nipHelp.style.color = '#3bb54a';
  }catch(e){
    nipHelp.textContent = 'Gagal mengambil data pegawai.';
    nipHelp.style.color = '#e74c3c';
  }
}

nip.addEventListener('blur', () => fetchPegawai(nip.value.trim()));
modePasien.addEventListener('change', () => {
  if(modePasien.value === 'ybs') setYBS();
  else setManual();
});
namaPasien.addEventListener('input', applyRules);

document.getElementById('formPendaftaran').addEventListener('submit', () => {
  tipePasien.disabled = false;
  hubKel.disabled = false;
});
</script>
@endsection
