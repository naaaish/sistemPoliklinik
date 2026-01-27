@extends('layouts.kepegawaian')

@section('content')
<div class="page-header">
    <h4>Data Pegawai</h4>

    <div class="page-actions">
        <button class="btn-upload-csv" onclick="openCsvModal()">
            Upload CSV
        </button>

        <a href="{{ route('pegawai.create') }}" class="btn-tambah">
            Tambah Pegawai
        </a>
    </div>
</div>



@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>✗ Error!</strong> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="table-box">
    {{-- Search Form --}}
    <form method="GET" action="{{ route('kepegawaian.pegawai') }}" class="pegawai-search">
        <input 
            type="text" 
            name="q" 
            value="{{ $q ?? request('q') }}" 
            placeholder="Cari nama pegawai..." 
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
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Jabatan</th>
                <th>Bidang</th>
                <th class="pegawai-cell-center">Lihat</th> 
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai as $p)
            <tr>
                <td>{{ $p->nip }}</td>
                <td>{{ $p->nama_pegawai }}</td>
                <td>{{ $p->jabatan }}</td>
                <td>{{ $p->bagian }}</td>
                <td class="pegawai-cell-center">
                    <a href="{{ route('kepegawaian.pegawai.show', $p->nip) }}" class="view-btn">
                        <img src="{{ asset('assets/adminPoli/eye.png') }}" alt="Lihat">
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="pegawai-empty">
                    {{ request('q') ? 'Tidak ada pegawai ditemukan dengan nama "' . request('q') . '"' : 'Belum ada data pegawai' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- MODAL UPLOAD CSV --}}
<div id="csvModal" class="csv-modal-overlay">
    <div class="csv-modal-box">
        <div class="csv-modal-header">
            <h5 class="mb-0">Import Data Pegawai / Keluarga</h5>
            <button type="button" class="btn-close-modal" onclick="closeCsvModal()">&times;</button>
        </div>
        <form action="{{ route('pegawai.import.multi') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="csv-modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Tipe Data</label>
                    <select name="type" class="form-control" required>
                        <option value="pegawai">Data Pegawai</option>
                        <option value="keluarga">Data Keluarga</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">File CSV</label>
                    <input type="file" name="file" class="form-control" accept=".csv" required>
                </div>

                <div class="csv-info">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Pastikan urutan kolom CSV sesuai format. NIP harus terdaftar jika pilih Data Keluarga.</span>
                </div>
            </div>
            <div class="csv-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCsvModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Import CSV</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCsvModal() {
    document.getElementById('csvModal').classList.add('show');
}

function closeCsvModal() {
    document.getElementById('csvModal').classList.remove('show');
}

function closeOnOverlay(event) {
    if (event.target === event.currentTarget) {
        closeCsvModal();
    }
}

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s ease-out';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 5000);
</script>

@endsection