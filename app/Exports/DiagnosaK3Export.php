<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class DiagnosaK3Export extends StringValueBinder implements FromCollection, WithEvents, WithStyles, WithColumnWidths, WithDefaultStyles, WithCustomValueBinder
{
    public function __construct(private array $rows) {}

    public function collection()
    {
        $out = [];
        $out[] = ['Nomor', 'Jenis Penyakit'];

        foreach ($this->rows as $r) {
            if ($r->tipe === 'penyakit' && preg_match('/^lainnya sebutkan/i', trim((string)$r->nama_penyakit))) {
                continue;
            }

            if ($r->tipe === 'kategori') {
                $out[] = [$r->id_nb, $r->kategori_penyakit];
            } else {
                $out[] = [$r->id_nb, $r->nama_penyakit];
            }
        }

        return new Collection($out);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 90,
        ];
    }

    public function defaultStyles(Style $defaultStyle)
    {
        return $defaultStyle->getFont()->setName('Arial')->setSize(11);
    }

    public function styles(Worksheet $sheet)
    {
        // wrap text kolom B
        $sheet->getStyle('B:B')->getAlignment()->setWrapText(true);

        return [
            1 => [ // header row
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E6E6E6'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();

                // Border kotak semua sel yang kepake
                $range = "A1:B{$highestRow}";
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                    ],
                ]);

                // kolom A center
                $sheet->getStyle("A1:A{$highestRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Bold + center untuk baris kategori (id_nb tanpa titik)
                for ($r = 2; $r <= $highestRow; $r++) {
                    $no = (string)$sheet->getCell("A{$r}")->getValue();
                    if ($no !== '' && preg_match('/^\d+$/', $no)) {
                        $sheet->getStyle("A{$r}:B{$r}")->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }
            }
        ];
    }
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'A' && $cell->getRow() >= 2) {
            $cell->setValueExplicit((string)$value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}