<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductIntake extends Model
{

    use HasFactory;

    protected $fillable = [
        'model_name', 'sku', 'imei_number', 'serial_number', 'sale_price', 'retail_price', 'retail_price', 'invoice_number', 'supplier_person', 'delivery_person', 'receiving_person', 'delivery_men_id', 'product_service_id', 'type', 'created_by'
    ];


    public static $the_status = [
        'All Products',
        //0
        'received',
        //1
        'instock',
        //2
        'sold',
        //3
        'invoiced',
        //4
        'paid',
        //5
        'shopreturn',
        //6
        'custreturn'
        //7
    ];

    // protected $casts=[
    //     'model_name'=>'array'
    // ];

    public function deliveryman()
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id', 'id');
    }


    public function productservice()
    {

        return $this->belongsTo(ProductService::class, 'product_service_id', 'id');
    }

    

    public static function productdeliveryperson($delivery_person)
    {
        $del_personArr  = explode(',', $delivery_person);
        // dd($del_personArr);
        $del_personRate = 0;
        foreach ($del_personArr as $delivery_person) {
            $delivery_person    = DeliveryMan::find($delivery_person);
            // dd($delivery_person);
            $fname= $delivery_person->first_name;
            // dd($fname);
            $lname = $delivery_person->last_name;
            // dd($lname);
            $bothnames=$fname . " ". $lname;
       
            $del_personRate        = isset($delivery_person) ? $bothnames: '';
            // dd($del_personRate);
        }

        return $del_personRate;
    }
}

