<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductIntake extends Model
{
    use HasFactory;

    protected $fillable=[
        'model_name','sku','imei_number','serial_number','sale_price','retail_price','retail_price','invoice_number','type','created_by'
    ];

    // protected $casts=[
    //     'model_name'=>'array'
    // ];

    
}
