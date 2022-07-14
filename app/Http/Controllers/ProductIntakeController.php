<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Vender;
use App\Models\CustomField;
use App\Models\DeliveryMan;
use App\Exports\DailyReport;
use Illuminate\Http\Request;
use App\Models\ProductIntake;
use App\Models\ProductService;
use Illuminate\Validation\Rule;
use App\Models\ProductServiceUnit;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductIntakeExport;
use App\Imports\ProductIntakeImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProductServiceCategory;
use App\Http\Requests\ProductIntakeStoreRequest;

class ProductIntakeController extends Controller
{
    public function Filter_order_imei(Request $request)
    {

        // $data=ProductIntake::select('imei_number')->where('model_name',$request->the_model_name)->get();
        $select = $request->select;
        $value = $request->value;
        $dependent = $request->dependent;

        $data = DB::table('product_intakes')
            ->where($select, $value)
            ->groupBy($dependent)
            ->get();

        // $output = '<option value="" class="select2"> Select ' . ucfirst($dependent) . '</option>';

        $output = '<select class="select2"><option value=""> Select ' . ucfirst($dependent) . '</option></select>';

        foreach ($data as $row) {
            $output .= '<option value="' . $row->$dependent . '">' . $row->$dependent . '</option>';
        }
        // echo $output; 

        return response()->json($output);
    }

    public function filter_product_serial_number(Request $request)
    {
        $select = $request->select;
        $value = $request->value;
        $dependent = $request->dependent;
        $data = DB::table('product_intakes')
        ->where($select, $value)
            ->groupBy($dependent)
            ->get();

        $output = '<select class="select2"><option value=""> Select ' . ucfirst($dependent) . '</option></select>';

        foreach ($data as $row) {
            $output .= '<option value="' . $row->$dependent . '">' . $row->$dependent . '</option>';
        }

        return response()->json($output);
    }

    
    public function getVenderId(Request $request)
    {
        $prodc = Vender::select('id')->where('name', $request->supplier_person)->first();
        return response()->json($prodc);
    }

    public function getDeliveyManId(Request $request)
    {
        $prodc = DeliveryMan::select('id','first_name','last_name')->where('contact', $request->delivery_person)->first();
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
            ->where('status', '=', 'instock')
            ->pluck('count');
        // return $total;
        return response()->json($total);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (\Auth::user()->can('manage product & service')) {

            $productIntakes = ProductIntake::where('created_by', '=', \Auth::user()->creatorId())->get();

            if ($request->has('search')) {

                $productIntakes = ProductIntake::where('serial_number', 'like', "%{$request->search}%")->orWhere('imei_number', 'like', "%{$request->search}%")->get();
            }
            // $ret= $productIntakes;
            // dd($productIntakes);
            return view('productIntake.index', compact('productIntakes'));
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


            $product_model_name         = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'name');
            $supplier_person            = Vender::all()->pluck('name', 'name');
            $delivery_person            = DeliveryMan::all()->pluck('contact', 'contact');
            $delivery_person_concat = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->pluck('name', 'contact');
            $receiving_person            = \Auth::user()->name;
            // dd($receiving_person);
            return view('productIntake.create', compact('product_model_name', 'supplier_person', 'delivery_person', 'receiving_person', 'delivery_person_concat', 'customFields'));
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
                'imei_number' => 'unique:product_intakes',
                'serial_number' => 'unique:product_intakes',
                'product_service_id' => 'required',
                'delivery_man_id' => 'required',
                'vender_id' => 'required',
                'sale_price' => 'required|numeric',
                'retail_price' => 'required|numeric',
                'color' => 'required',
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

            // $productIntake->model_name      = !empty($request->model_name) ? implode(',', $request->model_name) : '';
            $productIntake->model_name = $request->model_name;
            if ($request->imei_number) {
                $productIntake->imei_number     = $request->imei_number;
            }
            if ($request->imei_number_down) {
                $productIntake->imei_number     = $request->imei_number_down;
            }
            if ($request->serial_number) {
                $productIntake->serial_number   = $request->serial_number;
            }
            if ($request->serial_number_down) {
                $productIntake->serial_number   = $request->serial_number_down;
            }

            $productIntake->product_service_id   = $request->product_service_id;
            $productIntake->delivery_man_id      = $request->delivery_man_id;
            $productIntake->vender_id            = $request->vender_id;
            $productIntake->sale_price           = $request->sale_price;
            $productIntake->retail_price         = $request->retail_price;
            $productIntake->color                = $request->color;
            $productIntake->status               = 'received';
            $productIntake->supplier_person      = $request->supplier_person;
            $productIntake->delivery_person      = $request->delivery_person;
            $productIntake->receiving_person     = $request->receiving_person;

            $productIntake->created_by           = \Auth::user()->creatorId();
            // $time = \Carbon\Carbon::now();
            // $dateonly = date("Y-m-d", strtotime($time));
            // $productIntake->updated_at           = $dateonly;

            $productIntake->save();

            // return response()->json($productIntake);

            CustomField::saveData($productIntake, $request->customField);

            return redirect()->route('productintake.index')->with('success', __('Product(s) successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function storeData(Request $request)
    {
        //
        $Record = new ProductIntake();

        $Record->model_name = $request->model_name;
        if ($request->imei_number) {
            $Record->imei_number     = $request->imei_number;
        }
        if ($request->imei_number_down) {
            $Record->imei_number     = $request->imei_number_down;
        }
        if ($request->serial_number) {
            $Record->serial_number   = $request->serial_number;
        }
        if ($request->serial_number_down) {
            $Record->serial_number   = $request->serial_number_down;
        }
        // $Record->imei_number = $request->imei_number_down;
        // $Record->serial_number = $request->serial_number_down;
        $Record->product_service_id = $request->product_service_id;
        $Record->delivery_man_id = $request->delivery_man_id;
        $Record->sale_price = $request->sale_price;
        $Record->retail_price = $request->retail_price;
        $Record->status = "received";
        $Record->supplier_person = $request->supplier_person;
        $Record->delivery_person = $request->delivery_person;
        $Record->receiving_person = $request->receiving_person;
        $Record->created_by      = \Auth::user()->creatorId();
        // $time = \Carbon\Carbon::now();
        // $dateonly = date("Y-m-d", strtotime($time));
        // $Record->updated_at      = $dateonly;

        $Record->save();
        if ($Record->save()) {
            return response()->json(['success' => __('Product Saved.')], 200);
            // return response()->json($Record);

        } else {
            return response()->json(['success' => __('Failed.')], 401);
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

        if (\Auth::user()->can('edit product & service')) {

            if ($productIntake->created_by == \Auth::user()->creatorId()) {
                $product_model_name         = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'name');
                $the_supplier_person        = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'name');
                $the_delivery_person        = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('contact', 'contact');
                // $the_receiving_person        = ::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'name');

                $del_person  = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->pluck('name', 'contact');
                // dd($del_person);
                $delivery_person_concat = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->get();
                $my_delivery_person = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->pluck('name', 'contact');

                // dd($delivery_person_concat);


                return view('productIntake.edit', compact('product_model_name', 'productIntake', 'the_supplier_person', 'the_delivery_person', 'del_person', 'delivery_person_concat', 'my_delivery_person'));
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
        // dump($id);
        // dd($request->all());

        if (\Auth::user()->can('edit product & service')) {
            $productIntake = ProductIntake::find($id);
            // dd($productIntake);
            $rules = [
                'model_name' => 'required',
                'imei_number' => 'unique:product_intakes,imei_number,' . $id,
                'serial_number' => 'unique:product_intakes,serial_number,' . $id,

                // 'imei_number' => [

                //     Rule::unique('product_intakes', 'imei_number')->ignore($request->id),
                // ],
                // 'serial_number' => [
                //     'required',
                //     Rule::unique('product_intakes', 'serial_number')->ignore($request->id),
                // ],
                'product_service_id' => 'required',
                'sale_price' => 'required|numeric',
                'retail_price' => 'required|numeric',
                'color' => 'required',
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

            if ($request->imei_number != '') {
                $productIntake->imei_number     = $request->imei_number;
            } else {
                $productIntake->imei_number     = null;
            }
            $productIntake->serial_number   = $request->serial_number;
            $productIntake->product_service_id   = $request->product_service_id;
            $productIntake->delivery_man_id   = $request->delivery_man_id;
            $productIntake->vender_id   = $request->vender_id;
            $productIntake->quantity_delivered   =  $productIntake->quantity_delivered;

            $productIntake->sale_price      = $request->sale_price;
            $productIntake->retail_price    = $request->retail_price;
            $productIntake->color    = $request->color;
            $productIntake->returned  = $productIntake->returned;
            $productIntake->status          = $productIntake->status;
            $productIntake->supplier_person  = $request->supplier_person;
            $productIntake->delivery_person  = $request->delivery_person;
            $productIntake->receiving_person  = $request->receiving_person;
            $productIntake->receiving_person  = \Auth::user()->name;
            $productIntake->returning_person  = $productIntake->returning_person;
            // $time = \Carbon\Carbon::now();

            // $dateonly = date("Y-m-d", strtotime($time));
            $productIntake->created_by      = \Auth::user()->creatorId();
            // $productIntake->updated_at      = $dateonly;
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
