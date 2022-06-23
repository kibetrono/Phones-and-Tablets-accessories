<?php

namespace App\Http\Controllers;

use App\Models\Vender;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use App\Models\ProductIntake;
use App\Models\ProductReturn;
use App\Models\ProductService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductReturnController extends Controller
{

    public function getProductDetails(Request $request)
    {
        $prodc = ProductIntake::select('model_name', 'imei_number', 'invoice_number','sale_price', 'retail_price', 'supplier_person')->where('id', $request->serial_number)->first();
        return response()->json($prodc);
    }


    public function getDeliveryPerson(Request $request)
    {
        $prodc = ProductIntake::with('deliveryman')->select('delivery_person','delivery_man_id')->where('id', $request->serial_number)->first();
        // $products = $prodc->deliveryman; //belongsTo relationship

        // $prodc = ProductIntake::select(DB::raw("CONCAT(first_name,' ',last_name) AS bothNames"), 'delivery_man_id')->where('id', $request->serial_number)->first();
        return response()->json($prodc);
        // return json_encode(['products' => $products]);

    }

    public function getDeliveryPersonTwo(Request $request)
    {
      
        $prodc = DeliveryMan::select('id', 'contact')->Where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"),
            'LIKE',
            "%" . $request->returning_person. "%"
        )->first();
        // $query->orWhere(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%" . $this->returning_person . "%");
    // dd($prodc);
        return response()->json($prodc);
        // return json_encode(['products' => $products]);

    }

    public function getReceivingPerson(Request $request)
    {
        $prodc = ProductIntake::select('receiving_person')->where('id', $request->serial_number)->first();
        return response()->json($prodc);
    }


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

            $product_serial_number      = ProductIntake::where('status', '!=', 'shopreturn')->pluck('serial_number', 'id');
            // $product_serial_number      = ProductIntake::all()->pluck('serial_number', 'id');
            // $product_delivery_person    = DeliveryMan::all()->pluck('first_name', 'id');
            $product_delivery_person    = DeliveryMan::all();
            
            // dd($product_delivery_person);

            $product_model_name     = ProductService::all()->pluck('name', 'name');
            // $product_sku            = ProductService::all()->pluck('sku', 'sku');
            $product_imei_no        = ProductIntake::all()->pluck('imei_number', 'imei_number');
            // $product_serial_no      = ProductIntake::where('returned', '=', 0)->pluck('serial_number', 'id');
            // $product_serial_no      = ProductIntake::all()->pluck('serial_number', 'id');
            $suppliers_name         = Vender::all()->pluck('name', 'name');
            // $prodc = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS bothNames"));
            // dd($prodc);
            $delivery_person_concat = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->pluck('name', 'contact');
            $delivery_person_concat2 = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact', 'id')->get();

        //    $another= DeliveryMan::selectRaw("CONCAT (first_name,' ',last_name) as columns, tax_number")->pluck('columns', 'tax_number');
            $users = DB::table('delivery_men')->select("*", DB::raw("CONCAT(delivery_men.first_name,' ',delivery_men.last_name) AS full_name"))->get();
            // dd($delivery_person_concat);

            $person_receiving_in_shop      = Auth::user()->name;
            // print_r($person_receiving->name);
            // print_r($person_receiving_in_shop);

            return view('productreturn.create', compact('product_model_name', 'product_imei_no', 'product_serial_number', 'suppliers_name', 'person_receiving_in_shop', 'delivery_person_concat', 'delivery_person_concat2', 'product_delivery_person','customFields', 'users'));
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

        // dd($request->all());

   

        if (\Auth::user()->can('edit product & service')) {

            $productIntake = ProductIntake::find($request->serial_number);

            // dd($productIntake);

            $rules = [
                'model_name'         => 'required',
                'imei_number'        => 'required',
                'serial_number'      => 'required',
                // 'delivery_man_id' => 'required',
                'returning_person_id' =>'required',
                // 'quantity_delivered' => 'required',
                'sale_price'         => 'required',
                'retail_price'       => 'required',
                // 'returned'           => 'required',
                // 'invoice_number' => 'required',
                'supplier_person'    => 'required',
                // 'delivery_person'    => 'required',
                'returning_person'    => 'required',
                'receiving_person'   => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('productintake.index')->with('error', $messages->first());
            }

            if ($productIntake->created_by == \Auth::user()->creatorId()) {


                $productIntake->id                    = $productIntake->id;
                $productIntake->model_name            = $request->model_name;
                $productIntake->imei_number           = $request->imei_number;
                $productIntake->serial_number         = $productIntake->serial_number;
                $productIntake->product_service_id    = $productIntake->product_service_id;
                $productIntake->delivery_man_id       = $productIntake->delivery_man_id;
                $productIntake->returning_person_id   = $request->returning_person_id;
                
                $productIntake->quantity_delivered    = 1;
                $productIntake->sale_price            = $request->sale_price;
                $productIntake->retail_price          = $request->retail_price;
                $productIntake->invoice_number        = $request->invoice_number;
                $productIntake->returned              = 0;
                $productIntake->status                = 'shopreturn';
                $productIntake->supplier_person       = $request->supplier_person;
                $productIntake->delivery_person       = $productIntake->delivery_person;
                $productIntake->returning_person       = $request->returning_person;
                $productIntake->receiving_person      = $request->receiving_person;
                $productIntake->created_by            = \Auth::user()->creatorId();
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
