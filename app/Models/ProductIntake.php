<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductIntake extends Model
{
    use HasFactory;

    protected $fillable=[
        'model_name','sku','imei_number','serial_number','sale_price','retail_price','retail_price','invoice_number', 'supplier_person','delivery_person','receiving_person','delivery_men_id','type','created_by'
    ];

    public function deliveryman(){
        return $this->belongsTo(DeliveryMan::class);
    }

    public static $the_status= [
        'Received',
        //0
        'In Stock',
        //1
        'Sold',
        //2
        'Invoiced',
        //3
        'Paid',
        //4
    ];

    // protected $casts=[
    //     'model_name'=>'array'
    // ];

    
}
