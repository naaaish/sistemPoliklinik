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
            ->leftJoin('diagnosa_k3', function ($join) {
                $join->on('diagnosa.id_nb', '=', 'diagnosa_k3.id_nb')
                    ->where('diagnosa_k3.is_active', 1)
                    ->where('diagnosa_k3.tipe', 'penyakit');
            })
            ->where('diagnosa.is_active', 1)
            ->whereBetween('diagnosa.created_at', [$this->from, $this->to])
            ->orderBy('diagnosa.diagnosa')
            ->get([
                'diagnosa.id_diagnosa',
                'diagnosa.diagnosa',
                'diagnosa.id_nb',
                'diagnosa_k3.nama_penyakit as nama_k3',
                'diagnosa.created_at',
            ]);
    }

    public function headings(): array
    {
        return ['ID Diagnosa', 'Diagnosa', 'NB', 'Nama Diagnosa K3', 'Created At'];
    }
}
