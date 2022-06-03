<?php

namespace App\Exports;

use App\Models\ProductIntake;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductIntakeExport implements FromCollection, WithHeadings
{

    public function collection()
    {
        $data = ProductIntake::get();

        foreach ($data as $k => $ProductIntake) {
            $taxe  = ProductIntake::Taxe($ProductIntake->tax_id);
            $unit  = ProductIntake::productserviceunit($ProductIntake->unit_id);
            $category  = ProductIntake::productcategory($ProductIntake->category_id);


            unset($ProductIntake->created_by, $ProductIntake->imei_number,$ProductIntake->serial_number, $ProductIntake->updated_at, $ProductIntake->created_at);
            $data[$k]["tax_id"]       = $taxe;
            $data[$k]["unit_id"]       = $unit;
            $data[$k]["category_id"]   = $category;
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "ID",
            "IMEI No.",
            "Serial No.",
            "sale_price",
            "Recommended sale_price",
            "Invoice No.",
        ];
    }
}
