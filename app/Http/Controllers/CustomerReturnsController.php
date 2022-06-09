<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomField;
use Illuminate\Http\Request;
use App\Models\ProductIntake;
use App\Models\CustomerReturns;

class CustomerReturnsController extends Controller
{

    public function getModelName(Request $request)
    {        
        $prodc = ProductIntake::select('model_name')->where('serial_number', $request->serial_number)->first();
        return response()->json($prodc);
    }

    public function getImeiNumber(Request $request)
    {
        $prodc = ProductIntake::select('imei_number')->where('serial_number', $request->serial_number)->first();
        return response()->json($prodc);
    }

    public function getInvoiceNumber(Request $request)
    {
        $prodc = ProductIntake::select('invoice_number')->where('serial_number', $request->serial_number)->first();
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
            $pro = ProductIntake::all();

            $product_serial_number      = ProductIntake::all()->pluck('serial_number', 'serial_number')->toArray();
            // $ppp= $product_serial_number->prepend('Please Select');
            $returning_customer         = Customer::all()->pluck('name', 'name')->toArray();
            $receiving_person           = \Auth::user()->name;
            // $services = ProductIntake::all();
            // return view('customerreturns.create', compact('services'));
                    // return view('customerreturns.create2', compact('otheritems'));

            return view('customerreturns.create', compact('product_serial_number', 'returning_customer', 'receiving_person', 'customFields','pro'));
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

            $rules = [
                'model_name' => 'required',
                'imei_number' => 'required',
                'serial_number' => 'required',
                'quantity_delivered' => 'required',
                // 'invoice_number' => 'required',
                'returning_customer' => 'required',
                'receiving_person' => 'required',
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
            $customerReturns->quantity_delivered   = $request->quantity_delivered;
            $customerReturns->invoice_number  = $request->invoice_number;
            $customerReturns->returning_customer  = $request->returning_customer;
            $customerReturns->receiving_person  = $request->receiving_person;
            $customerReturns->created_by      = \Auth::user()->creatorId();
            $customerReturns->save();
            CustomField::saveData($customerReturns, $request->customField);

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
    public function destroy(CustomerReturns $customerReturns)
    {
        //
    }
}
