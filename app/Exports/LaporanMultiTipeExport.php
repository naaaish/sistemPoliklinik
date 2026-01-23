<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanMultiTipeExport implements WithMultipleSheets
{
    public function __construct(private array $sheetsData) {}

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->sheetsData as $sd) {
            $sheets[] = new LaporanSheetView(
                $sd['sheet'],
                $sd['tipe'],
                $sd['from'],
                $sd['to'],
                $sd['rows']
            );
        }
        return $sheets;
    }
}
