<?php

namespace App\Exports;

use App\Models\ProductIntake;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReportExport implements FromCollection, WithHeadings
{
    protected $data;

    function __construct($data)
    {

        $this->data = $data;
    }
    public function collection()
    {
        // dd($this->data);
        return $this->data;

    }

    public function headings(): array
    {
        return [
            "ID",
            "Name",

        ];
    }

}
