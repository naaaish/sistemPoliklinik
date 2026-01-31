@extends('layouts.kepegawaian')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pegawai-form.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="pegawai-form-wrapper">

        {{-- PAGE HEADER --}}
        <div class="page-header">
            <div class="d-flex align-items-center">
                <h4 class="mb-0">{{ $mode == 'create' ? 'Tambah Anggota Keluarga' : 'Edit Anggota Keluarga' }}</h4>
            </div>
        </div>

        

        {{-- FORM CARD --}}
        <div class="card pegawai-form-card">
            <div class="card-body">
                <form 
                    action="{{ $mode == 'create' ? route('keluarga.store') : route('keluarga.update', $keluarga->id_keluarga) }}"
                    method="POST"
                >
                    @csrf
                    @if($mode == 'edit') @method('PUT') @endif

                    <div class="row g-3">
                        {{-- NIP PEGAWAI (READONLY) --}}
                        <div class="col-md-6">
                            <div class="form-row-custom">
                                <label class="form-label-custom">NIP Pegawai</label>
                                <input type="text" name="nip" class="form-control-custom bg-light" 
                                       value="{{ $pegawai->nip }}" readonly>
                            </div>
                        </div>

                        {{-- NAMA ANGGOTA KELUARGA --}}
                        <div class="col-md-6">
                            <div class="form-row-custom">
                                <label class="form-label-custom">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama_keluarga" class="form-control-custom"
                                       value="{{ old('nama_keluarga', $keluarga->nama_keluarga ?? '') }}" required>
                            </div>
                        </div>

                        {{-- HUBUNGAN KELUARGA --}}
                        <div class="col-md-6">
                            <div class="form-row-custom">
                                <label class="form-label-custom">Hubungan <span class="text-danger">*</span></label>
                                <select name="hubungan_keluarga" class="form-control-custom" id="hubungan_select" required>
                                    <option value="">-- Pilih Hubungan --</option>
                                    <option value="pasangan" {{ old('hubungan_keluarga', $keluarga->hubungan_keluarga ?? '') == 'pasangan' ? 'selected' : '' }}>Pasangan (Suami/Istri)</option>
                                    <option value="anak" {{ old('hubungan_keluarga', $keluarga->hubungan_keluarga ?? '') == 'anak' ? 'selected' : '' }}>Anak</option>
                                </select>
                            </div>
                        </div>

                        {{-- URUTAN ANAK (Hanya muncul jika hubungan = anak) --}}
                        <div class="col-md-6" id="urutan_anak_row" style="display: none;">
                            <div class="form-row-custom">
                                <label class="form-label-custom">Anak Ke-</label>
                                <input type="number" name="urutan_anak" class="form-control-custom" min="1"
                                    value="{{ old('urutan_anak', $nextChildNumber ?? '') }}">
                            </div>
                        </div>

                        {{-- JENIS KELAMIN --}}
                        <div class="col-md-6">
                            <div class="form-row-custom">
                                <label class="form-label-custom">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-control-custom" required>
                                    <option value="">-- Pilih --</option>
                                    {{-- Value L/P sesuai database --}}
                                    <option value="L" {{ old('jenis_kelamin', $keluarga->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $keluarga->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                        </div>

                        {{-- TANGGAL LAHIR --}}
                        <div class="col-md-6">
                            <div class="form-row-custom">
                                <label class="form-label-custom">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_lahir" class="form-control-custom"
                                       value="{{ old('tgl_lahir', $keluarga->tgl_lahir ?? '') }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- BUTTONS SECTION - BALANCED & MODERN --}}
                    <div class="form-actions">
                        {{-- Left Side - Back Button --}}
                        <div class="form-actions-left">
                            <a href="{{ route('pegawai.show', $pegawai->nip) }}" class="btn-form btn-back-detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                                </svg>
                                Kembali ke Detail Pegawai
                            </a>
                        </div>

                        {{-- Right Side - Action Buttons --}}
                        <div class="form-actions-right">
                            <a href="{{ route('pegawai.show', $pegawai->nip) }}" class="btn-form btn-batal">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                Batal
                            </a>
                            
                            <button type="submit" class="btn-form btn-simpan">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                Simpan Data Keluarga
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Logic untuk menampilkan/menyembunyikan urutan anak
    const hubunganSelect = document.getElementById('hubungan_select');
    const urutanRow = document.getElementById('urutan_anak_row');

    function toggleUrutanAnak() {
        if (hubunganSelect.value === 'anak') {
            urutanRow.style.display = 'block';
        } else {
            urutanRow.style.display = 'none';
        }
    }

    hubunganSelect.addEventListener('change', toggleUrutanAnak);
    
    // Jalankan saat load (untuk mode edit)
    window.addEventListener('DOMContentLoaded', toggleUrutanAnak);

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#ffffff',
                iconColor: '#ef4444',
                customClass: {
                    popup: 'admin-toast'
                }
            });
        @endif
    });
</script>
@endsection