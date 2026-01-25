@extends('layouts.kepegawaian')

@section('content')
<div class="pegawai-form-wrapper">

    {{-- HEADER --}}
    <div class="page-header mb-4">
        <div class="page-header-left">
            <a href="{{ route('pegawai.index') }}" class="btn-back-icon">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
            </a>

            <h4 class="page-title">
                {{ $mode == 'create' ? 'Tambah Pegawai' : 'Edit Pegawai' }}
            </h4>
        </div>
    </div>


    <div class="card pegawai-form-card">
        <div class="card-body">
            <form
                action="{{ $mode == 'create'
                    ? route('pegawai.store')
                    : route('pegawai.update', $pegawai->nip) }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                {{-- FOTO --}}
                <div class="foto-wrapper">
                    <div class="foto-preview">
                        @if(!empty($pegawai->foto))
                            <img src="{{ asset('storage/foto_pegawai/'.$pegawai->foto) }}">
                        @else
                            <span>Foto</span>
                        @endif
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Foto Pegawai</div>
                    <input type="file" name="foto" class="form-control-custom">
                </div>

                {{-- IDENTITAS --}}
                <div class="form-row-custom">
                    <div class="form-label-custom">NIP</div>
                    <input type="text" name="nip" class="form-control-custom"
                        value="{{ $pegawai->nip ?? '' }}"
                        {{ $mode == 'edit' ? 'readonly' : '' }} required>
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Nama Lengkap</div>
                    <input type="text" name="nama_pegawai" class="form-control-custom"
                        value="{{ $pegawai->nama_pegawai ?? '' }}" required>
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">NIK</div>
                    <input type="text" name="nik" class="form-control-custom"
                        value="{{ $pegawai->nik ?? '' }}">
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Agama</div>
                    <input type="text" name="agama" class="form-control-custom"
                        value="{{ $pegawai->agama ?? '' }}">
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Jenis Kelamin</div>
                    <select name="jenis_kelamin" class="form-control-custom">
                        <option value="">-- pilih --</option>
                        <option value="Laki-laki" {{ ($pegawai->jenis_kelamin ?? '')=='Laki-laki'?'selected':'' }}>Laki-laki</option>
                        <option value="Perempuan" {{ ($pegawai->jenis_kelamin ?? '')=='Perempuan'?'selected':'' }}>Perempuan</option>
                    </select>
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Tanggal Lahir</div>
                    <input type="date" name="tgl_lahir" class="form-control-custom"
                        value="{{ $pegawai->tgl_lahir ?? '' }}">
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Tanggal Masuk</div>
                    <input type="date" name="tgl_masuk" class="form-control-custom"
                        value="{{ $pegawai->tgl_masuk ?? '' }}">
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Status Pernikahan</div>
                    <select name="status_pernikahan" class="form-control-custom">
                        <option value="">-- pilih --</option>
                        <option value="Menikah" {{ ($pegawai->status_pernikahan ?? '')=='Menikah'?'selected':'' }}>Menikah</option>
                        <option value="Belum Menikah" {{ ($pegawai->status_pernikahan ?? '')=='Belum Menikah'?'selected':'' }}>Belum Menikah</option>
                    </select>
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">No. Telepon</div>
                    <input type="text" name="no_telp" class="form-control-custom"
                        value="{{ $pegawai->no_telp ?? '' }}">
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Email</div>
                    <input type="email" name="email" class="form-control-custom"
                        value="{{ $pegawai->email ?? '' }}">
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Alamat</div>
                    <textarea name="alamat" class="form-control-custom">{{ $pegawai->alamat ?? '' }}</textarea>
                </div>

                {{-- PEKERJAAN --}}
                <div class="form-row-custom">
                    <div class="form-label-custom">Jabatan</div>
                    <input type="text" name="jabatan" class="form-control-custom"
                        value="{{ $pegawai->jabatan ?? '' }}" required>
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Bagian</div>
                    <input type="text" name="bagian" class="form-control-custom"
                        value="{{ $pegawai->bagian ?? '' }}" required>
                </div>

                {{-- PENDIDIKAN --}}
                <div class="form-row-custom">
                    <div class="form-label-custom">Pendidikan Terakhir</div>
                    <input type="text" name="pendidikan_terakhir" class="form-control-custom"
                        value="{{ $pegawai->pendidikan_terakhir ?? '' }}">
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Institusi</div>
                    <input type="text" name="institusi" class="form-control-custom"
                        value="{{ $pegawai->institusi ?? '' }}">
                </div>

                <div class="form-row-custom">
                    <div class="form-label-custom">Tahun Lulus</div>
                    <input type="text" name="thn_lulus" class="form-control-custom"
                        value="{{ $pegawai->thn_lulus ?? '' }}">
                </div>

                {{-- STATUS --}}
                <div class="form-row-custom">
                    <div class="form-label-custom">Status Akun</div>
                    <select name="is_active" class="form-control-custom status-akun">
                        <option value="1" {{ ($pegawai->is_active ?? 1)==1?'selected':'' }}>🟢 Aktif</option>
                        <option value="0" {{ ($pegawai->is_active ?? 1)==0?'selected':'' }}>🔴 Non Aktif</option>
                    </select>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
