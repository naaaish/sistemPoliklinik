<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ObatExport implements FromView
{
    protected $data;
    protected $from;
    protected $to;

    public function __construct($data, $from, $to)
    {
        $this->data = $data;
        $this->from = $from;
        $this->to   = $to;
    }

    public function view(): View
    {
        return view('adminpoli.obat.export_excel', [
            'data' => $this->data,
            'from' => $this->from,
            'to'   => $this->to,
        ]);
    }
}
