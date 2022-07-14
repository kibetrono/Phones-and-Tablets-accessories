<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Order;
use App\Models\Vender;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use App\Models\ProductIntake;
use App\Models\ProductService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function Dynamic_Dependent(Request $request)
    {
        $select=$request->get('select');
        $value = $request->get('value');;
        $dependent=$request->get('dependent');;

        $data=DB::table('product_intakes')
        ->where($select, $value)
        ->groupBy($dependent)
        ->get();
        

        $output='<option value=""> Select '.ucfirst($dependent). '</option>';

        foreach($data as $row){
            $output .= '<option value="'.$row->$dependent. '">' .$row->$dependent . '</option>';
        }
        // echo $output; 

        return response()->json($output);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
       $alldata=DB::table('product_intakes')->groupBy('model_name')->get();
        // $alldata = ProductIntake::all();
        
       return view('order.dynamic_dependent',compact('alldata'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {


        
        // $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        // $plan    = Plan::find($planID);
        

        if (\Auth::user()->can('create product & service')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();
            // $product_model_name         = ProductIntake::where([['created_by', '=', \Auth::user()->creatorId()],['status','!=','sold']])->get()->pluck('model_name', 'model_name');
            $product_model_name         = ProductIntake::where('status', '!=', 'sold')->get();
            // dd($product_model_name);

            $product_imei_number        = ProductIntake::where('status','!=','sold')->pluck('imei_number', 'imei_number');
            $customer_name=Customer::all()->pluck('name','name');
            $payment_type=[
                'Mobile Payments',
                'Cash',
                'Personal Cheque',
                'Debit Card',
                'Credit Card',
                'Bank',
            ];

            $payment_types=array_values($payment_type);
            // dd($payment_types);


            return view('order.create', compact('product_model_name', 'product_imei_number','customer_name', 'payment_types', 'customFields'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        dd($request->all());

        if(\Auth::user()){

            $rules=[
                'model_name'=>'required',
                'imei_number' => 'required',
                'sale_price' => 'required',
                'payment_type' => 'required',
                'customer_name' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('order.index')->with('error', $messages->first());
            }

            $order_id=time();

            $order= new Order();
            $order->order_id= $order_id;
            $order->name = '';
            $order->email = '';
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = '';
            $order->plan_id = '';
            $order->model_name= $request->model_name;
            $order->imei_number = $request->imei_number;
            $order->quantity = 1;
            $order->price = $request->sale_price;
            $order->price_currency = !empty(env('CURRENCY')) ? env('CURRENCY') : 'USD';

            $order->txn_id = '';
            $order->payment_status = '';
            $order->payment_type = $request->payment_type;
            $order->receipt = '';
            $order->user_id = \Auth::user()->id;
            $order->customer_name = $request->customer_name;
            $order->invoice_number = $request->invoice_number;
            
            $order->save();

            if($order->save()){

                ProductIntake::Where('imei_number',$request->imei_number)->update(array('status'=>'sold'));

              $prod= ProductService::Where('name', $request->model_name)->first();
        
                ProductService::Where('name', $request->model_name)->update(array('quantity' => $prod->quantity-1));
            }

            return redirect()->route('order.index')->with('success','Order Successfully Placed');

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
