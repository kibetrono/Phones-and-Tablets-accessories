<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration',
        'max_users',
        'max_customers',
        'max_venders',
        'max_deliverymen',
        'description',
        'image',
    ];

    public static $arrDuration = [
        'unlimited' => 'Unlimited',
        'month' => 'Per Month',
        'year' => 'Per Year',
    ];

    public function status()
    {
        return [
            __('Unlimited'),
            __('Per Month'),
            __('Per Year'),
        ];
    }

    public static function total_plan()
    {
        return Plan::count();
    }

    public static function most_purchese_plan()
    {
        $free_plan = Plan::where('price', '<=', 0)->first()->id;

        return User:: select(DB::raw('count(*) as total'))->where('type', '=', 'company')->where('plan', '!=', $free_plan)->groupBy('plan')->first();
    }
}
