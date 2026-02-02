@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pegawai.css') }}">
<link rel="stylesheet" href="{{ asset('css/kelolauser.css') }}">
<link rel="stylesheet" href="{{ asset('css/modal-reset-password.css') }}">

<div class="page-header">
    <h4>Kelola User</h4>

    <div class="page-actions">
        <button class="btn-upload-csv" onclick="openCsvModal()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Upload CSV
        </button>
    </div>
</div>

<div class="table-box table-wrapper">
    {{-- Search Form --}}
    <form method="GET" action="{{ route('kepegawaian.kelolaUser.index') }}" class="pegawai-search">
        <input 
            type="text" 
            name="q" 
            value="{{ $q ?? request('q') }}" 
            placeholder="Cari username / nama / NIP..." 
            class="pegawai-search-input"
        >
        <button type="submit" class="pegawai-search-btn">
            <img src="{{ asset('assets/adminPoli/search.png') }}" class="pegawai-search-icon" alt="cari">
            <span>Cari</span>
        </button>
    </form>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Nama User</th>
                <th>Role</th>
                <th>NIP</th>
                <th class="pegawai-cell-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr>
                <td>{{ $u->username }}</td>
                <td>{{ $u->nama_user }}</td>
                <td>{{ ucfirst($u->role) }}</td>
                <td>{{ $u->nip ?? '-' }}</td>
                <td class="pegawai-cell-center">
                    <button 
                        class="btn-reset-password" 
                        onclick="openUserModal({{ $u->id }})"
                        title="Reset Password"
                    >
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Reset Pass
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="pegawai-empty">
                    {{ request('q') ? 'Tidak ada user ditemukan dengan kata kunci "' . request('q') . '"' : 'Belum ada data user' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="pagination-container">
        <form method="GET" class="per-page-selector">
            <label>Tampilkan</label>
            <select name="per_page" onchange="this.form.submit()">
                @foreach ([10,25,50,100] as $size)
                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>
            @foreach(request()->except('per_page','page') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
        </form>

        <div class="pagination-info">
            Menampilkan
            <strong>{{ $users->firstItem() ?? 0 }}</strong> -
            <strong>{{ $users->lastItem() ?? 0 }}</strong>
            dari
            <strong>{{ $users->total() }}</strong> data
        </div>

        <div class="pagination-nav">
            {{ $users->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

{{-- =================== MODAL UPLOAD CSV =================== --}}
<div id="csvModal" class="csv-modal-overlay">
    <div class="csv-modal-box">
        <div class="csv-modal-header">
            <h5>Import Data User</h5>
            <button type="button" class="btn-close-modal" onclick="closeCsvModal()">&times;</button>
        </div>
        
        <form action="{{ route('kepegawaian.kelolaUser.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="csv-modal-body">
                <div class="mb-3">
                    <label class="form-label">File CSV</label>
                    <input type="file" name="file" class="form-control" accept=".csv" required>
                </div>

                <div class="csv-info">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <div>
                        <strong>Format CSV:</strong><br>
                        username, password, role, nama_user, nip<br>
                        <small>Contoh: budi123, password123, pasien, Budi Santoso, 198765432001</small>
                    </div>
                </div>
            </div>
            
            <div class="csv-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCsvModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Import CSV</button>
            </div>
        </form>
    </div>
</div>

{{-- =================== MODAL RESET PASSWORD (READ-ONLY DISPLAY) =================== --}}
<div class="user-modal-overlay" id="userModal">
    <div class="user-modal-box">
        <div class="user-modal-header">
            <h5>Reset Password User</h5>
            <button type="button" class="btn-close-modal" onclick="closeUserModal()">&times;</button>
        </div>

        <form id="formResetPassword" onsubmit="return updatePassword(event)">
            <div class="user-modal-body">
                
                {{-- INFO USER (READ-ONLY) --}}
                <div class="user-info-display">
                    <div class="info-item">
                        <label class="info-label">Username</label>
                        <div class="info-value" id="displayUsername">-</div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">Nama User</label>
                        <div class="info-value" id="displayNamaUser">-</div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">Role</label>
                        <div class="info-value" id="displayRole">-</div>
                    </div>

                    <div class="info-item">
                        <label class="info-label">NIP</label>
                        <div class="info-value" id="displayNip">-</div>
                    </div>
                </div>

                <div class="divider-line"></div>

                {{-- FORM PASSWORD BARU --}}
                <div class="mb-3">
                    <label class="form-label">
                        Password Baru 
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="newPassword" 
                        name="password" 
                        placeholder="Minimal 6 karakter"
                        required
                        minlength="6"
                    >
                    <small class="form-hint">Kosongkan jika tidak ingin mengubah password</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Konfirmasi Password Baru 
                        <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="confirmPassword" 
                        name="password_confirmation" 
                        placeholder="Ulangi password baru"
                        required
                        minlength="6"
                    >
                    <small class="form-hint">Pastikan password sama dengan yang di atas</small>
                </div>

                {{-- Hidden Input untuk ID User --}}
                <input type="hidden" id="userId" name="user_id">
            </div>

            <div class="user-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUserModal()">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ========== MODAL RESET PASSWORD ==========
function openUserModal(userId) {
    document.getElementById('displayUsername').textContent = 'Loading...';
    document.getElementById('displayNamaUser').textContent = 'Loading...';
    document.getElementById('displayRole').textContent = 'Loading...';
    document.getElementById('displayNip').textContent = 'Loading...';

    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('userId').value = userId;

    document.getElementById('userModal').classList.add('show');

    fetch(`/kepegawaian/kelola-user/${userId}`)
        .then(res => {
            if (!res.ok) throw new Error('Gagal load data');
            return res.json();
        })
        .then(data => {
            document.getElementById('displayUsername').textContent = data.username ?? '-';
            document.getElementById('displayNamaUser').textContent = data.nama_user ?? '-';
            document.getElementById('displayRole').textContent = data.role ? ucfirst(data.role) : '-';
            document.getElementById('displayNip').textContent = data.nip ?? '-';
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Tidak dapat memuat data user'
            });
            closeUserModal();
        });
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
}

// ========== UPDATE PASSWORD ==========
function updatePassword(event) {
    event.preventDefault();
    
    const userId = document.getElementById('userId').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.style.zIndex = '1000000'; // Agar muncul di depan modal
        }
    });

    if (!newPassword || !confirmPassword) {
        Toast.fire({ icon: 'warning', title: 'Password tidak boleh kosong!' });
        return;
    }

    if (newPassword !== confirmPassword) {
        Toast.fire({ icon: 'error', title: 'Konfirmasi password tidak cocok!' });
        return;
    }

    // Ubah teks tombol jadi Loading
    const btnSubmit = document.querySelector('.user-modal-footer .btn-primary');
    const originalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    // URL HARUS menyertakan prefix /kepegawaian/ sesuai web.php
    fetch(`/kepegawaian/kelola-user/${userId}/reset-password`, { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            password: newPassword,
            password_confirmation: confirmPassword
        })
    })
    .then(async response => {
        const data = await response.json();
        if (response.ok && data.status) {
            Toast.fire({ icon: 'success', title: data.message || 'Password berhasil diperbarui' });
            closeUserModal(); // Pastikan fungsi ini ada untuk tutup modal
            setTimeout(() => location.reload(), 1500); // Reload agar data bersih
        } else {
            throw new Error(data.message || 'Terjadi kesalahan pada server');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toast.fire({ icon: 'error', title: error.message });
    })
    .finally(() => {
        // Kembalikan tombol ke status awal
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;
    });
}
// ========== UTIL ==========
function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// klik luar modal → close
document.getElementById('userModal').addEventListener('click', function (e) {
    if (e.target === this) closeUserModal();
});
</script>

@endpush