<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomField;
use App\Models\DeliveryMan;
use App\Models\ProductIntake;
use Illuminate\Http\Request;
use App\Models\ProductReturn;
use App\Models\ProductService;
use App\Models\Vender;
use Illuminate\Support\Facades\Auth;

class ProductReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
          
            $product_model_name     = ProductService::all()->pluck('name', 'name');
            // $product_sku            = ProductService::all()->pluck('sku', 'sku');
            $product_imei_no        = ProductIntake::all()->pluck('imei_number', 'imei_number');
            $product_serial_no      = ProductIntake::where('returned', '=', 0)->pluck('serial_number', 'id');
            // $product_serial_no      = ProductIntake::all()->pluck('serial_number', 'id');
            $suppliers_name         = Vender::all()->pluck('name', 'name');
            $deliveryPersons_name   = DeliveryMan::all()->pluck('name', 'name');
            $person_receiving_in_shop      = Auth::user()->name;
            // print_r($person_receiving->name);
            // print_r($person_receiving_in_shop);

            return view('productreturn.create', compact('product_model_name', 'product_imei_no', 'product_serial_no', 'suppliers_name', 'deliveryPersons_name', 'person_receiving_in_shop','customFields'));
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
        // $productIntake = ProductIntake::find($request->serial_number);
        // dd($productIntake);

        if (\Auth::user()->can('edit product & service')) {
            $productIntake = ProductIntake::find($request->serial_number);


            if ($productIntake->created_by == \Auth::user()->creatorId()) {
                // dd($productIntake);

                // $productIntake->update([

                //     'returned' => $request->returned,

                // ]);
                // $productIntake->update($request->all());

                $productIntake->id     = $productIntake->id;
                $productIntake->model_name      = $productIntake->model_name;
                $productIntake->imei_number     = $productIntake->imei_number;
                $productIntake->serial_number   = $productIntake->serial_number;
                $productIntake->quantity_delivered   = $productIntake->quantity_delivered;
                // dd($productIntake->model_name);
                $productIntake->sale_price      = $productIntake->sale_price;
                $productIntake->retail_price    = $productIntake->retail_price;
                $productIntake->invoice_number  = $productIntake->invoice_number;

                $productIntake->returned        = 1;
                $productIntake->created_by     = \Auth::user()->creatorId();
                $productIntake->save();


                return redirect()->route('productintake.index')->with('success', __('Product intakes successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductReturn  $ProductReturn
     * @return \Illuminate\Http\Response
     */
    public function show(ProductReturn $ProductReturn)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductReturn  $ProductReturn
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductReturn $ProductReturn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductReturn  $ProductReturn
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductReturn $ProductReturn, $id)
    {
        // print_r($id);
        // dd($request->all());
        if (\Auth::user()->can('edit product & service')) {
            $productService = ProductService::find($id);
            if ($request->quantity_type == 'Add') {
                $total = $productService->quantity + $request->quantity;
            } else {
                $total = $productService->quantity - $request->quantity;
            }

            if ($productService->created_by == \Auth::user()->creatorId()) {
                $productService->quantity        = $total;
                $productService->created_by     = \Auth::user()->creatorId();
                $productService->save();

                //Product Stock Report
                $type = 'manually';
                $type_id = 0;
                $description = $request->quantity . '  ' . __('quantity added by manually');
                Utility::addProductStock($productService->id, $request->quantity, $type, $description, $type_id);


                return redirect()->route('productstock.index')->with('success', __('Product quantity updated manually.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductReturn  $ProductReturn
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductReturn $ProductReturn)
    {
        //
    }
}
