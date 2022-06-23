<?php

namespace App\Http\Controllers;

use App\Exports\DailyReport;
use App\Models\Tax;
use App\Models\Vender;
use App\Models\CustomField;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use App\Models\ProductIntake;
use App\Models\ProductService;
use App\Models\ProductServiceUnit;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductIntakeExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductIntakeImport;
use App\Models\ProductServiceCategory;
use App\Http\Requests\ProductIntakeStoreRequest;

class ProductIntakeController extends Controller
{

    public function getDeliveyManId(Request $request)
    {
        $prodc = DeliveryMan::select('id')->where('contact', $request->delivery_person)->first();
        return response()->json($prodc);
    }

    public function getModelNameId(Request $request)
    {
        $prodc = ProductService::select('id')->where('name', $request->model_name)->first();
        // $prodc = ProductService::all();
        return response()->json($prodc);
    }

    public function totalProductsInStock()
    {
        $total = ProductIntake::groupBy('status')
        ->selectRaw('count(*) as count, status')
        ->where('status','=', 'instock')
        ->pluck('count');
        // return $total;
        return response()->json($total);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // $prods=DeliveryMan::all();

        // return view('test.index2', compact('prods'));


        // $products = \App\Models\ProductService::with('productservice')->get();
        // $products =ProductService::find(1);

        // $ser=$products->productintake;
        
        // dd($ser);

        // return view('test.index', compact('products'));

        // $productService        = \App\Models\DeliveryMan::with('theproductintakes')->get();

        // $productService        = \App\Models\ProductIntake::with('productservice')->get();


        // $per = $productService;

        // dd($productService);

        // $it=ProductIntake::$the_status;
        // dd($it);

        if (\Auth::user()->can('manage product & service'))
        {
            // $productIntakes = ProductIntake::where([['created_by', '=', \Auth::user()->creatorId()],['returned','=',0]])->get();
            $productIntakes = ProductIntake::where('created_by', '=', \Auth::user()->creatorId())->get();

            // $ret= $productIntakes;
            // dd($productIntakes);
            return view('productIntake.index', compact('productIntakes'));
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
            $supplier_person            = Vender::all()->pluck('name','name');
            $delivery_person            = DeliveryMan::all()->pluck('contact', 'contact');
            $delivery_person_concat = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->pluck('name', 'contact');
            $receiving_person            = \Auth::user()->name;
            // dd($receiving_person);
            return view('productIntake.create', compact('product_model_name', 'supplier_person', 'delivery_person', 'receiving_person', 'delivery_person_concat','customFields'));
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
                'product_service_id' => 'required',
                
                'delivery_man_id' => 'required',

                'sale_price' => 'required|numeric',
                'retail_price' => 'required|numeric',
                // 'invoice_number' => 'required',
                'supplier_person' => 'required',
                'delivery_person' => 'required',
                'receiving_person' => 'required',
                
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
            $productIntake->product_service_id   = $request->product_service_id;
            $productIntake->delivery_man_id = $request->delivery_man_id;
            $productIntake->sale_price      = $request->sale_price;
            $productIntake->retail_price    = $request->retail_price;
            $productIntake->invoice_number  = $request->invoice_number;
            $productIntake->status          = 'received';
            $productIntake->supplier_person  = $request->supplier_person;
            $productIntake->delivery_person  = $request->delivery_person;
            $productIntake->receiving_person  = $request->receiving_person;
            // $productIntake->receiving_person  = \Auth::user()->name;

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
                $the_supplier_person        = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'name');
                $the_delivery_person        = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('contact', 'contact');
                // $the_receiving_person        = ::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'name');
                
                $del_person  = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->pluck('name', 'contact');
                // dd($del_person);
                $delivery_person_concat = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->get();
                $my_delivery_person= DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->pluck('name', 'contact');

                // dd($delivery_person_concat);


                return view('productIntake.edit', compact( 'product_model_name','productIntake', 'the_supplier_person', 'the_delivery_person', 'del_person', 'delivery_person_concat', 'my_delivery_person'));
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
        // dd($request->all());

        if (\Auth::user()->can('edit product & service')) {
            $productIntake = ProductIntake::find($id);
            $rules = [
                'model_name' => 'required',
                'imei_number' =>'required',
                'serial_number' =>'required',
                'product_service_id'=>'required',
                'sale_price' => 'required|numeric',
                'retail_price' => 'required|numeric',
                // 'invoice_number' => 'required',
                'supplier_person' => 'required',
                'delivery_person' => 'required',
                'receiving_person' => 'required',
            ];
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('productintake.index')->with('error', $messages->first());
            }
                // $productIntake->model_name      = !empty($request->model_name) ? implode(',', $request->model_name) : '';
                $productIntake->id      = $productIntake->id;
                $productIntake->model_name      = $request->model_name;
                $productIntake->imei_number     = $request->imei_number;
                $productIntake->serial_number   = $request->serial_number;
                $productIntake->product_service_id   = $request->product_service_id;
                $productIntake->delivery_man_id   = $request->delivery_man_id;
                $productIntake->quantity_delivered   =  $productIntake->quantity_delivered;
            
                $productIntake->sale_price      = $request->sale_price;
                $productIntake->retail_price    = $request->retail_price;
                $productIntake->invoice_number  = $request->invoice_number;
                $productIntake->returned  = $productIntake->returned;
                $productIntake->status          = $productIntake->status;
                $productIntake->supplier_person  = $request->supplier_person;
                $productIntake->delivery_person  = $request->delivery_person;
                $productIntake->receiving_person  = $request->receiving_person;
               $productIntake->receiving_person  = \Auth::user()->name;

                $productIntake->returning_person  = $productIntake->returning_person;
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


    public function export()
    {

        $name = 'product_intake_' . date('Y-m-d i:h:s');

        $data = Excel::download(new ProductIntakeExport(), $name . '.xlsx');
       
        // return Excel::download(new ExportUser, 'users.xlsx');

        // dd($data);

        return $data;
    }

    public function importFile()
    {
        return view('productintake.import');
    }
  
    public function import(Request $request)
    {

        $rules = [
            'file' => 'required',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $products     = (new ProductIntakeImport)->toArray(request()->file('file'))[0];
        $totalProduct = count($products) - 1;
        $errorArray   = [];
        for ($i = 1; $i <= count($products) - 1; $i++) {
            $items  = $products[$i];

            $taxes     = explode(';', $items[5]);

            $taxesData = [];
            foreach ($taxes as $tax) {
                $taxes       = Tax::where('id', $tax)->first();
                //                $taxesData[] = $taxes->id;
                $taxesData[] = !empty($taxes->id) ? $taxes->id : 0;
            }

            $taxData = implode(',', $taxesData);
            //            dd($taxData);

            if (!empty($productBySku)) {
                $productService = $productBySku;
            } else {
                $productService = new ProductService();
            }

            $productService->name           = $items[0];
            $productService->sku            = $items[1];
            $productService->quantity       = $items[2];
            $productService->sale_price     = $items[3];
            $productService->purchase_price = $items[4];
            $productService->type           = $items[5];
            $productService->description    = $items[6];
            $productService->created_by     = \Auth::user()->creatorId();

            if (empty($productService)) {
                $errorArray[] = $productService;
            } else {
                $productService->save();
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {

            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalProduct . ' ' . 'record');


            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }
}

