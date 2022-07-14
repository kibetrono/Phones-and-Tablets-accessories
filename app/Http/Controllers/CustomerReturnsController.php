<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomField;
use Illuminate\Http\Request;
use App\Models\ProductIntake;
use App\Models\CustomerReturns;

class CustomerReturnsController extends Controller
{

    public function getproductData(Request $request)
    {        
        $prodc = ProductIntake::select('id','model_name', 'imei_number', 'invoice_number', 'status')
        ->where('id', $request->serial_number)->first();
        return response()->json($prodc);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->can('manage product & service')) {
            $customerReturns = CustomerReturns::where('created_by', '=', \Auth::user()->creatorId())->get();
            // $customerReturns = ProductIntake::where([['created_by', '=', \Auth::user()->creatorId()],['status','=','received']])->get();

            // dd($productIntakes);
            return view('customerreturns.index', compact('customerReturns'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->can('create product & service')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();
            $status= 'custreturn';
            // $product_serial_number      = ProductIntake::where('status', '!=', $status)->pluck('serial_number', 'serial_number')->toArray();
            $product_serial_number      = ProductIntake::where('status', '!=', $status)->pluck('serial_number', 'id');
            // $product_serial_number      = ProductIntake::whereIn('status', ['paid','sold'])->pluck('serial_number', 'id');
            $returning_customer         = Customer::all()->pluck('name', 'name')->toArray();
            $receiving_person           = \Auth::user()->name;
        
            return view('customerreturns.create', compact('product_serial_number', 'returning_customer', 'receiving_person', 'customFields'));
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
        // dd($request->all());
        if (\Auth::user()->can('create product & service')) {

            // $pr= ProductIntake::where('id',$request->product_id)->get();

            // dd($pr);

            $rules = [
                'model_name' => 'required',
                'imei_number' => 'required',
                'serial_number' => 'required',
                // 'quantity_delivered' => 'required',
                // 'invoice_number' => 'required',
                'returning_customer' => 'required',
                // 'receiving_person' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('customerreturns.index')->with('error', $messages->first());
            }

            $customerReturns                  = new CustomerReturns();

            $customerReturns->model_name      =  $request->model_name;
            $customerReturns->imei_number     = $request->imei_number;
            $customerReturns->serial_number   = $request->serial_number;
            $customerReturns->quantity_delivered   = 1;
            $customerReturns->returning_customer  = $request->returning_customer;
            $customerReturns->receiving_person  =\Auth::user()->name;
            $customerReturns->created_by      = \Auth::user()->creatorId();
            $customerReturns->save();
            CustomField::saveData($customerReturns, $request->customField);

            if($customerReturns->save()){
                // $time = \Carbon\Carbon::now();

                // $dateonly = date("Y-m-d", strtotime($time));

           ProductIntake::where('id',$request->product_id)->update(array('status'=>'custreturn'));

            };

            return redirect()->route('customerreturns.index')->with('success', __('Product successfully submitted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        } 
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerReturns  $customerReturns
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerReturns $customerReturns)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerReturns  $customerReturns
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerReturns $customerReturns)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerReturns  $customerReturns
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerReturns $customerReturns)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerReturns  $customerReturns
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('delete product & service')) {
            $cust_return = CustomerReturns::find($id);

            $cust_return->delete();

            return redirect()->route('customerreturns.index')->with('success', __('Product successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
