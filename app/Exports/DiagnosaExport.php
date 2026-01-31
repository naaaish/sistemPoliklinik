<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DiagnosaExport implements FromCollection, WithHeadings
{
    public function __construct(private string $from, private string $to) {}

    public function collection()
    {
        return DB::table('diagnosa')
            ->where('is_active', 1)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->orderBy('diagnosa')
            ->get([
                'id_diagnosa',
                'diagnosa',
                'keterangan',
                'klasifikasi_nama',
                'bagian_tubuh',
            ]);
    }

    public function headings(): array
    {
        return ['NO.', 'DIAGNOSA NAMA', 'KETERANGAN', 'KLASIFIKASI NAMA', 'BAGIAN TUBUH'];
    }
}