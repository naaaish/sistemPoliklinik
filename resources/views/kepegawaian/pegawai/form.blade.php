@extends('layouts.kepegawaian')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pegawai-form.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="pegawai-form-wrapper">

        <div class="page-header">
            <h4>{{ $mode == 'create' ? 'Tambah Pegawai' : 'Edit Pegawai' }}</h4>
            <a href="{{ route('pegawai.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✓ Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>✗ Error!</strong> Periksa form berikut:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

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
                        @if($mode == 'edit' && !empty($pegawai->foto))
                            <img src="{{ asset('storage/foto_pegawai/'.$pegawai->foto) }}?v={{ time() }}" alt="Foto" id="preview-img">
                        @else
                            <span id="preview-text">Foto Pegawai</span>
                            <img src="" alt="Preview" id="preview-img" style="display:none;">
                        @endif
                    </div>
                </div>

                <div class="mb-4 text-center">
                    <input type="file" name="foto" class="form-control w-50 mx-auto" accept="image/*" id="foto-input">
                    <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                </div>

                <div class="row g-3">
                    @if($mode == 'create')
                    <div class="col-md-6">
                        <label class="form-label">NIP <span class="text-danger">*</span></label>
                        <input type="text" name="nip" class="form-control" value="{{ old('nip') }}" required>
                    </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">Nama Pegawai <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pegawai" class="form-control"
                               value="{{ old('nama_pegawai', $pegawai->nama_pegawai ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik" class="form-control"
                               value="{{ old('nik', $pegawai->nik ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Agama</label>
                        <input type="text" name="agama" class="form-control"
                               value="{{ old('agama', $pegawai->agama ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" class="form-control"
                               value="{{ old('tgl_lahir', $pegawai->tgl_lahir ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Masuk</label>
                        <input type="date" name="tgl_masuk" class="form-control"
                               value="{{ old('tgl_masuk', $pegawai->tgl_masuk ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status Pernikahan</label>
                        <select name="status_pernikahan" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option value="Menikah" {{ old('status_pernikahan', $pegawai->status_pernikahan ?? '') == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                            <option value="Belum Menikah" {{ old('status_pernikahan', $pegawai->status_pernikahan ?? '') == 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">No Telp</label>
                        <input type="text" name="no_telp" class="form-control"
                               value="{{ old('no_telp', $pegawai->no_telp ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $pegawai->email ?? '') }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $pegawai->alamat ?? '') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="jabatan" class="form-control"
                               value="{{ old('jabatan', $pegawai->jabatan ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Bagian <span class="text-danger">*</span></label>
                        <input type="text" name="bagian" class="form-control"
                               value="{{ old('bagian', $pegawai->bagian ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" class="form-control"
                               value="{{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Institusi</label>
                        <input type="text" name="institusi" class="form-control"
                               value="{{ old('institusi', $pegawai->institusi ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tahun Lulus</label>
                        <input type="text" name="thn_lulus" class="form-control"
                               value="{{ old('thn_lulus', $pegawai->thn_lulus ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status Akun</label>
                        <select name="is_active" class="form-select status-akun">
                            <option value="1" {{ old('is_active', $pegawai->is_active ?? 1) == 1 ? 'selected' : '' }}>🟢 Aktif</option>
                            <option value="0" {{ old('is_active', $pegawai->is_active ?? 1) == 0 ? 'selected' : '' }}>🔴 Non Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('pegawai.index') }}" class="btn btn-secondary px-4 me-2">Batal</a>
                    <button type="submit" class="btn btn-success px-4">Simpan</button>
                </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('foto-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('preview-img');
            const previewText = document.getElementById('preview-text');
            
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            if (previewText) previewText.style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
});

setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s ease-out';
        alert.style.opacity = '0';
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 500);
    });
}, 5000);
</script>
@endsection