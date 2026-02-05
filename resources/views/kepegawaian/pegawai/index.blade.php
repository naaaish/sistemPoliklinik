@extends('layouts.kepegawaian')

@section('content')
<div class="page-header">
    <h4>Data Pegawai</h4>

    <div class="page-actions">
        <button class="btn-upload-csv" onclick="openCsvModal()">
            Upload Data Pegawai
        </button>

        <a href="{{ route('pegawai.create') }}" class="btn-tambah">
            Tambah Pegawai
        </a>
    </div>
</div>

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

    {{-- Pagination --}}
    <div class="pagination-container">
        <form method="GET" class="per-page-selector">
            <label>Tampilkan</label>

            <select name="per_page" onchange="this.form.submit()">
                @foreach ([10,25,50,100,'all'] as $size)
                    <option value="{{ $size }}"
                        {{ $perPage == $size ? 'selected' : '' }}>
                        {{ strtoupper($size) }}
                    </option>
                @endforeach
            </select>

            {{-- keep query lain --}}
            @foreach(request()->except('per_page','page') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
        </form>

        @if(!$isAll)
        <div class="pagination-info">
            Menampilkan
            <strong>{{ $pegawai->firstItem() }}</strong>
            -
            <strong>{{ $pegawai->lastItem() }}</strong>
            dari
            <strong>{{ $pegawai->total() }}</strong> data
        </div>
        @endif

        @if(!$isAll)
        <div class="pagination-nav">
            {{ $pegawai->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
        @endif

    </div>

</div>
@endsection

{{-- MODAL UPLOAD CSV --}}
<div id="csvModal" class="csv-modal-overlay" 
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999999; align-items:center; justify-content:center;"
    onclick="closeOnOverlay(event)">
    <div class="csv-modal-box" 
        style="position:relative; width:520px; max-width:calc(100% - 40px); background:white; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.3); padding:0;">
        <div class="csv-modal-header" style="padding:24px; border-bottom:2px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
            <h5 style="margin:0; font-size:20px; font-weight:700; color:#1e293b;">Import Data Pegawai / Keluarga</h5>
            <button type="button" class="btn-close-modal" onclick="closeCsvModal()" 
                style="width:32px; height:32px; border:none; background:#f1f5f9; border-radius:8px; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>
        <form action="{{ route('pegawai.import.multi') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="csv-modal-body" style="padding:24px;">
                <div class="mb-3" style="margin-bottom:20px;">
                    <label class="form-label" style="display:block; font-weight:600; font-size:14px; color:#334155; margin-bottom:8px;">Pilih Tipe Data</label>
                    <select name="type" class="form-control" required 
                        style="width:100%; padding:12px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:14px;">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="pegawai">Data Pegawai</option>
                        <option value="keluarga">Data Keluarga</option>
                    </select>
                </div>

                <div class="mb-3" style="margin-bottom:20px;">
                    <label class="form-label">File Excel / CSV</label>
                    <input type="file" name="file" class="form-control" accept=".csv, .xlsx, .xls" required
                        style="width:100%; padding:12px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:14px;">
                </div>

                <div class="csv-info" style="display:flex; gap:10px; padding:12px 16px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; font-size:13px; color:#1e40af;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Pastikan urutan kolom CSV sesuai format. NIP harus terdaftar jika pilih Data Keluarga.</span>
                </div>
            </div>
            <div class="csv-modal-footer" style="display:flex; gap:12px; justify-content:flex-end; padding:16px 24px; border-top:2px solid #e5e7eb;">
                <button type="button" class="btn btn-primary" onclick="closeCsvModal()"
                    style="padding:11px 24px; border:none; border-radius:10px; font-weight:600; font-size:14px; cursor:pointer; background:#64748b; color:white;">Batal</button>
                <button type="submit" class="btn btn-primary"
                    style="padding:11px 24px; border:none; border-radius:10px; font-weight:600; font-size:14px; cursor:pointer; background:#3b82f6; color:white; box-shadow:0 4px 12px rgba(59,130,246,0.3);">Import File</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openCsvModal() {
    const modal = document.getElementById('csvModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.zIndex = '999999';
        modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.classList.add('show');
    }
}

function closeCsvModal() {
    const modal = document.getElementById('csvModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
    }
}

function closeOnOverlay(event) {
    if (event.target === event.currentTarget) {
        closeCsvModal();
    }
}
</script>

{{-- TOAST NOTIFICATIONS - --}}
@if(session('success'))
<script>
  AdminPoliToast.fire({
    icon: 'success',
    title: @json(session('success'))
  });
</script>
@endif

@if(session('error'))
<script>
  AdminPoliToast.fire({
    icon: 'error',
    title: @json(session('error'))
  });
</script>
@endif

@endpush