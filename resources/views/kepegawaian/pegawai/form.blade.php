@extends('layouts.kepegawaian')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pegawai-form.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="pegawai-form-wrapper">

        <div class="page-header">
            <h4>{{ $mode == 'create' ? 'Tambah Pegawai' : 'Edit Pegawai' }}</h4>
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

                {{-- BUTTONS SECTION - BALANCED & MODERN --}}
                <div class="form-actions">
                    {{-- Left Side - Back to Detail Button --}}
                    <div class="form-actions-left">
                        @if($mode == 'edit')
                        @method('PUT')
                        <a href="{{ route('pegawai.show', $pegawai->nip) }}" class="btn-form btn-back-detail">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                            </svg>
                            Kembali ke Detail
                        </a>
                        @endif
                    </div>

                    {{-- Right Side - Action Buttons --}}
                    <div class="form-actions-right">
                        <a href="{{ $mode == 'create' ? route('pegawai.index') : route('pegawai.show', $pegawai->nip) }}" 
                           class="btn-form btn-batal">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Batal
                        </a>
                        
                        <button type="submit" class="btn-form btn-simpan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Simpan Data
                        </button>
                    </div>
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