<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReturns extends Model
{
    use HasFactory;

    protected $fillable=[
            'model_name',
            'imei_number',
            'serial_number',
            'invoice_number',
            'returning_customer',
            'receiving_person',
    ];
}
