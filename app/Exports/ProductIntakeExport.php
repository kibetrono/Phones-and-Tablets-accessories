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
            $delivery_pedrson  = ProductIntake::productdeliveryperson($ProductIntake->delivery_man_id);
            unset($ProductIntake->product_service_id, 
                 $ProductIntake->returning_person_id, 
                 $ProductIntake->quantity_delivered,
                 $ProductIntake->returned,
                 $ProductIntake->delivery_person,
                 $ProductIntake->returning_person,
                 $ProductIntake->created_by,
                 $ProductIntake->updated_at,
                 $ProductIntake->created_at);
           
            $data[$k]["delivery_man_id"]   = $delivery_pedrson;
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "ID",
            "Name",
            "IMEI No.",
            "Serial No.",
            "Delivery Person",
            "Sale Price",
            "Recommended Retail Price",
            "Invoice No.",
            "Status",
            "Supplier",
            "Person Receiving ",
        ];
    }
}
