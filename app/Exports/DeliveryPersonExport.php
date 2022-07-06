<?php

namespace App\Exports;

use App\Models\DeliveryMan;
use Maatwebsite\Excel\Concerns\FromCollection;

class DeliveryPersonExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = DeliveryMan::get();

        foreach ($data as $k => $deliveryperson) {
            unset($deliveryperson->id, $deliveryperson->avatar, $deliveryperson->is_active, $deliveryperson->password, $deliveryperson->created_at, $deliveryperson->updated_at, $deliveryperson->lang, $deliveryperson->created_by, $deliveryperson->email_verified_at, $deliveryperson->remember_token);
            $data[$k]["deliveryman_id"] = \Auth::user()->deliverymanNumberFormat($deliveryperson->deliveryman_id);
            $data[$k]["balance"]     = \Auth::user()->priceFormat($deliveryperson->balance);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "Delivery Person Id",
            "First Name",
            "Last Name",
            "Email",
            "Contact",
        ];
    }
}
