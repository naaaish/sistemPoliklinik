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

                <div class="row g-3">
                    @if($mode == 'create')
                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" class="form-control-custom" value="{{ old('nip') }}" required>
                        </div>
                    </div>
                    @endif

                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Nama Pegawai <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pegawai" class="form-control-custom"
                                   value="{{ old('nama_pegawai', $pegawai->nama_pegawai ?? '') }}" required>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control-custom">
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control-custom"
                                   value="{{ old('tgl_lahir', $pegawai->tgl_lahir ?? '') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Tanggal Masuk</label>
                            <input type="date" name="tgl_masuk" class="form-control-custom"
                                   value="{{ old('tgl_masuk', $pegawai->tgl_masuk ?? '') }}">
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">No Telp</label>
                            <input type="text" name="no_telp" class="form-control-custom"
                                   value="{{ old('no_telp', $pegawai->no_telp ?? '') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Email</label>
                            <input type="email" name="email" class="form-control-custom"
                                   value="{{ old('email', $pegawai->email ?? '') }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Alamat</label>
                            <textarea name="alamat" class="form-control-custom" rows="2">{{ old('alamat', $pegawai->alamat ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control-custom"
                                   value="{{ old('jabatan', $pegawai->jabatan ?? '') }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Bagian <span class="text-danger">*</span></label>
                            <input type="text" name="bagian" class="form-control-custom"
                                   value="{{ old('bagian', $pegawai->bagian ?? '') }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-row-custom">
                            <label class="form-label-custom">Status Akun</label>
                            <select name="is_active" class="form-control-custom status-akun">
                                <option value="1" {{ old('is_active', $pegawai->is_active ?? 1) == 1 ? 'selected' : '' }}>🟢 Aktif</option>
                                <option value="0" {{ old('is_active', $pegawai->is_active ?? 1) == 0 ? 'selected' : '' }}>🔴 Non Aktif</option>
                            </select>
                        </div>
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