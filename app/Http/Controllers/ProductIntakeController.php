<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\CustomField;
use Illuminate\Http\Request;
use App\Models\ProductIntake;
use App\Models\ProductService;
use App\Models\ProductServiceUnit;
use App\Models\ProductServiceCategory;
use App\Http\Requests\ProductIntakeStoreRequest;


class ProductIntakeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->can('manage product & service'))
        {
            $productServices = ProductIntake::where('created_by', '=', \Auth::user()->creatorId())->get();
            return view('productIntake.index', compact('productServices'));
        }
        else
        {
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

            $product_model_name         = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name','name');
    // dd($product_model_name);
            return view('productIntake.create', compact('product_model_name', 'customFields'));
        }
        else
        {
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
                'imei_number' =>'required',
                'serial_number' =>'required',
                'sale_price' => 'required|numeric',
                'retail_price' => 'required|numeric',
                // 'invoice_number' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('productintake.index')->with('error', $messages->first());
            }

            $productIntake                  = new ProductIntake();

            $productIntake->model_name      = !empty($request->model_name) ? implode(',', $request->model_name) : '';
            $productIntake->imei_number     = $request->imei_number;
            $productIntake->serial_number   = $request->serial_number;
            $productIntake->sale_price      = $request->sale_price;
            $productIntake->retail_price    = $request->retail_price;
            $productIntake->invoice_number  = $request->invoice_number;
            $productIntake->created_by      = \Auth::user()->creatorId();
            $productIntake->save();
            CustomField::saveData($productIntake, $request->customField);

            return redirect()->route('productintake.index')->with('success', __('Product successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        } 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductIntake  $productIntake
     * @return \Illuminate\Http\Response
     */
    public function show(ProductIntake $productIntake)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductIntake  $productIntake
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $productIntake = ProductIntake::find($id);

        if (\Auth::user()->can('edit product & service'))
        {
            
            if ($productIntake->created_by == \Auth::user()->creatorId())
            {
                $product_model_name         = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name','name');

                return view('productIntake.edit', compact( 'product_model_name','productIntake'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductIntake  $productIntake
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (\Auth::user()->can('edit product & service')) {
            $productIntake = ProductIntake::find($id);
            $rules = [
                'model_name' => 'required',
                'imei_number' =>'required',
                'serial_number' =>'required',
                'sale_price' => 'required|numeric',
                'retail_price' => 'required|numeric',
                // 'invoice_number' => 'required',
            ];
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('productintake.index')->with('error', $messages->first());
            }
                $productIntake->model_name      = !empty($request->model_name) ? implode(',', $request->model_name) : '';
                $productIntake->imei_number     = $request->imei_number;
                $productIntake->serial_number   = $request->serial_number;
                $productIntake->sale_price      = $request->sale_price;
                $productIntake->retail_price    = $request->retail_price;
                $productIntake->invoice_number  = $request->invoice_number;
                $productIntake->created_by      = \Auth::user()->creatorId();
                $productIntake->save();
                CustomField::saveData($productIntake, $request->customField);

                return redirect()->route('productintake.index')->with('success', __('Product successfully updated.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductIntake  $productIntake
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('delete product & service')) {
            $productIntake = ProductIntake::find($id);
            
            $productIntake->delete();

            return redirect()->route('productintake.index')->with('success', __('Product successfully deleted.'));
   
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
