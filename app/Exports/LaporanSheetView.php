<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanSheetView implements FromView, WithTitle
{
    public function __construct(
        private string $title,
        private string $tipe,
        private string $from,
        private string $to,
        private array $rows
    ) {}

    public function title(): string
    {
        return mb_substr($this->title, 0, 31);
    }

    public function view(): View
    {
        return view('adminpoli.laporan.export_excel', [
            'tipe' => $this->tipe,
            'from' => $this->from,
            'to'   => $this->to,
            'rows' => $this->rows,
        ]);
    }
}
