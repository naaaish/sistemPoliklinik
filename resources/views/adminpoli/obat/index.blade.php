@extends('layouts.adminpoli')

@section('title', 'Obat')

@section('content')
<div class="obat-page">

    {{-- Header: Back + Title + Tambah --}}
    <div class="obat-topbar">
        <div class="obat-left">
            <a href="{{ route('adminpoli.dashboard') }}" class="obat-back" title="Kembali">←</a>
            <div class="obat-heading">Obat</div>
        </div>
        
        <button type="button" class="obat-btn-add" onclick="openTambah()">
            <img src="{{ asset('assets/adminPoli/plus1.png') }}" alt="+" class="obat-ic">
            <span>Tambah</span>
        </button>
    </div>

    <div class="obat-card">

        {{-- Row 1: Search --}}
        <form class="obat-search" method="GET" action="{{ route('adminpoli.obat.index') }}">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Masukkan nama obat yang dicari"
                class="obat-search-input"
            >
            <button class="obat-search-btn" type="submit">
                <img src="{{ asset('assets/adminPoli/search.png') }}" alt="cari" class="obat-ic">
                <span>Cari</span>
            </button>
        </form>

        {{-- Row 2: Upload + Download (sesuai desain: baris kecil rapi) --}}
        <div class="obat-tools-row">

            {{-- Upload (tetap ada) --}}
            @if(\Illuminate\Support\Facades\Route::has('adminpoli.obat.import'))
                <form action="{{ route('adminpoli.obat.import') }}" method="POST" enctype="multipart/form-data" class="obat-upload">
                    @csrf
                    <label class="obat-file">
                        <input type="file" name="file" accept=".csv,.xlsx,.xls">
                        <span>Pilih File</span>
                    </label>

                    <button type="submit" class="obat-btn-soft">
                        <span>Upload</span>
                    </button>
                </form>
            @else
                {{-- kalau route belum dibuat, tetap tampil tapi nonaktif (biar nggak error) --}}
                <div class="obat-upload obat-disabled">
                    <div class="obat-file"><span>Pilih File</span></div>
                    <button type="button" class="obat-btn-soft" disabled>Upload</button>
                </div>
            @endif

            {{-- Download + rentang tanggal --}}
            <form action="{{ route('adminpoli.obat.export') }}" method="GET" class="obat-download">
                <input type="date" name="from" value="{{ request('from') }}" class="obat-date" required>
                <span class="obat-sep">s/d</span>
                <input type="date" name="to" value="{{ request('to') }}" class="obat-date" required>

                <select name="format" class="obat-select" required>
                    <option value="" disabled {{ request('format') ? '' : 'selected' }}>Pilih Format</option>
                    <option value="csv"  {{ request('format')=='csv' ? 'selected' : '' }}>CSV</option>
                    <option value="excel"{{ request('format')=='excel' ? 'selected' : '' }}>Excel</option>
                    <option value="pdf"  {{ request('format')=='pdf' ? 'selected' : '' }}>PDF</option>
                </select>

                <button type="submit" class="obat-btn-soft">
                    <img src="{{ asset('assets/adminPoli/download.png') }}" alt="download" class="obat-ic">
                    <span>Download</span>
                </button>
            </form>
        </div>

        @if(request('from') && request('to'))
            <div class="obat-preview">
                <span>
                    {{ $previewCount ?? 0 }} data obat ditemukan
                    ({{ request('from') }} s/d {{ request('to') }})
                </span>
            </div>
        @endif

        {{-- Table --}}
        <div class="obat-table">
            <div class="obat-table-head">
                <div>Nama</div>
                <div>Harga Satuan</div>
                <div>Exp Date</div>
                <div>Aksi</div>
            </div>

            <div class="obat-table-body">
                @forelse($obat as $row)
                    @php
                        $pk = $row->id_obat ?? $row->kode_obat ?? null;
                        $nama = $row->nama_obat ?? $row->nama ?? '-';
                        $harga = $row->harga ?? null;
                        $exp = $row->exp_date ?? $row->expired_at ?? '-';
                    @endphp

                    <div class="obat-row">
                        <div><div class="obat-cell">{{ $nama }}</div></div>
                        <div><div class="obat-cell obat-center">{{ $harga ? 'Rp'.number_format($harga,0,',','.') : '-' }}</div></div>
                        <div><div class="obat-cell obat-center">{{ $exp }}</div></div>

                        <div class="obat-actions">
                            <button
                            type="button"
                            class="obat-act obat-edit"
                            onclick="openEdit('{{ $pk }}','{{ $nama }}','{{ $harga }}','{{ $exp }}')"> 
                            <img src="{{ asset('assets/adminPoli/edit.png') }}" alt="edit" class="obat-ic-sm">
                                Edit
                            </button>

                            <form method="POST" action="{{ route('adminpoli.obat.destroy', $pk) }}" class="obat-del-form"
                                  onsubmit="return confirm('Yakin hapus obat ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="obat-act obat-del">
                                    <span>Hapus</span>
                                    <img src="{{ asset('assets/adminPoli/sampah.png') }}" alt="hapus" class="obat-ic-sm">
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                <div class="obat-row obat-row-empty">
                    <div class="obat-empty-span">
                        {{ request('q') ? 'Tidak ada obat ditemukan' : 'Belum ada data obat' }}
                    </div>
                </div>
                @endforelse

            </div>
        </div>

        {{-- ================= MODAL TAMBAH OBAT ================= --}}
        <div class="modal-overlay" id="modalTambah">
            <div class="modal-card">
                <h3>Tambah Data Obat</h3>

                <form action="{{ route('adminpoli.obat.store') }}" method="POST">
                    @csrf

                    <div class="modal-group">
                        <label>Nama Obat</label>
                        <input type="text" name="nama_obat" required>
                    </div>

                    <div class="modal-group">
                        <label>Harga Satuan</label>
                        <input type="number" name="harga" min="1" step="1" required>
                    </div>

                    <div class="modal-group">
                        <label>Expired Date</label>
                        <input type="date" name="exp_date" id="editExp" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    </div>

                    <button type="submit" class="modal-btn">Simpan</button>
                </form>
            </div>
        </div>

        {{-- ================= MODAL EDIT OBAT ================= --}}
        <div class="modal-overlay" id="modalEdit">
            <div class="modal-card">
                <h3>Edit Data Obat</h3>

                <form method="POST" id="formEdit">
                    @csrf
                    @method('PUT')

                    <div class="modal-group">
                        <label>Nama Obat</label>
                        <input type="text" name="nama_obat" id="editNama" required>
                    </div>

                    <div class="modal-group">
                        <label>Harga Satuan</label>
                        <input type="number" name="harga" id="editHarga" required>
                    </div>

                    <div class="modal-group">
                        <label>Expired Date</label>
                        <input type="date" name="exp_date" id="editExp" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    </div>

                    <button type="submit" class="modal-btn">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="obat-foot">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>

</div>
@endsection

<script>
    function openTambah(){
        document.getElementById('modalTambah').style.display = 'flex';
    }

    function openEdit(id, nama, harga, exp){
        document.getElementById('modalEdit').style.display = 'flex';

        document.getElementById('editNama').value = nama;
        document.getElementById('editHarga').value = harga;
        document.getElementById('editExp').value = exp;

        document.getElementById('formEdit').action =
            "{{ url('adminpoli/obat') }}/" + id;
    }

    window.onclick = function(e){
        if(e.target.classList.contains('modal-overlay')){
            e.target.style.display = 'none';
        }
    }
</script>
