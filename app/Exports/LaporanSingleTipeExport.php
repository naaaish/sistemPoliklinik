<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanSingleTipeExport implements WithMultipleSheets
{
    public function __construct(
        private string $tipe,
        private string $from,
        private string $to,
        private array $rows
    ) {}

    public function sheets(): array
    {
        return [
            new LaporanSheetView(ucfirst($this->tipe), $this->tipe, $this->from, $this->to, $this->rows)
        ];
    }
}
