        {{-- DATA RESEP OBAT --}}
        <h2 class="section-title">Data Resep Obat</h2>
        <table class="resep-table">
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th>Nama Obat</th>
                    <th width="120">Jumlah</th>
                    <th width="150">Harga</th>
                    <th width="150">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @forelse($detailResep as $index => $item)
                @php 
                    $subtotal = $item->jumlah * $item->harga;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}.</td>
                    <td>{{ $item->nama_obat }}</td>
                    <td>{{ $item->jumlah }} {{ $item->satuan ?? 'Pcs' }}</td>
                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #6c757d; padding: 20px;">Tidak ada resep obat</td>
                </tr>
                @endforelse
                
                @if($detailResep->isNotEmpty())
                <tr class="total-row">
                    <td colspan="4" style="text-align: right; font-weight: 600;">TOTAL</td>
                    <td style="font-weight: 700;">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>