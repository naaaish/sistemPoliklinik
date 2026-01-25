<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 18px 18px 22px 18px; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11.5pt; }

    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
    th { background: #E6E6E6; text-align: center; font-weight: bold; }

    .col-no { width: 12%; text-align: center; }
    .col-jenis { width: 88%; }

    .cat td { font-weight: bold; text-align: center; }
    .cat td.col-jenis { text-transform: uppercase; letter-spacing: .3px; }

    /* DomPDF aman pakai inline/hex */
    .mark-b { color: #C00000; font-weight: bold; }
    .mark-l { color: #00A651; font-weight: bold; }
  </style>
</head>
<body>

@php
  function fmtPenyakit($text){
    $t = e((string)$text);
    $t = str_replace('(B)', '<span class="mark-b">(B)</span>', $t);
    $t = str_replace('(L)', '<span class="mark-l">(L)</span>', $t);
    return $t;
  }
@endphp

<table>
  <thead>
    <tr>
      <th class="col-no">Nomor</th>
      <th class="col-jenis">Jenis Penyakit</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $r)

      {{-- skip "Lainnya sebutkan..." --}}
      @continue($r->tipe === 'penyakit' && preg_match('/^lainnya sebutkan/i', trim((string)$r->nama_penyakit)))

      @if($r->tipe === 'kategori')
        <tr class="cat">
          <td class="col-no">{{ $r->id_nb }}</td>
          <td class="col-jenis">{{ $r->kategori_penyakit }}</td>
        </tr>
      @else
        <tr>
          <td class="col-no">{{ $r->id_nb }}</td>
          <td class="col-jenis">{!! fmtPenyakit($r->nama_penyakit) !!}</td>
        </tr>
      @endif

    @empty
      <tr>
        <td colspan="2">Tidak ada data.</td>
      </tr>
    @endforelse
  </tbody>
</table>

</body>
</html>