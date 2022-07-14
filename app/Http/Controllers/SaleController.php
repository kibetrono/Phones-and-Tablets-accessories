<?php

namespace App\Http\Controllers;

use Auth;
use Validator;
use App\Models\Sale;
use App\Exports\SaleExport;
use App\Models\CustomField;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use App\Exports\SaleExpoport;
use App\Models\ProductIntake;
use App\Models\ProductService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(\Auth::user()->can('manage product & service')){
            $sales = Sale::where('created_by', '=', \Auth::user()->creatorId())->latest()->get();
            return view('sale.index',compact('sales'));
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
            $customFields               = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();

            $product_model_name         = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'name');
            $delivery_person            = DeliveryMan::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'contact')->pluck('name', 'contact');
            $product_serial_number      = ProductIntake::where('status','!=','sold')->pluck('serial_number', 'serial_number');
            return view('sale.create', compact('product_model_name', 'delivery_person', 'product_serial_number','customFields'));
            
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
                'serial_number' => 'unique:sales',
                'sale_price' => 'required|numeric',
                'color' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'contacts' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('sale.index')->with('error', $messages->first());
            }

            $sale                       = new Sale();

            $sale->order_id             = time();
           
            if ($request->delivery_man_id != '') {
                $sale->delivery_man_id  = $request->delivery_man_id;
            } else {
                $sale->delivery_man_id  = null;
            }

            $sale->first_name           = $request->first_name;
            $sale->last_name            = $request->last_name;
            $sale->contacts             = $request->contacts;
            $sale->model_name           = $request->model_name;
            $sale->serial_number        = $request->serial_number;
            $sale->sale_price           = $request->sale_price;
            $sale->color                = $request->color;
            $sale->status               = 'sold';

            if ($request->delivery_man_names != '') {
                $sale->delivery_person  = $request->delivery_man_names;
            }else{
                $sale->delivery_person  = null;
            }
            
            $sale->receiving_person     =\Auth::user()->name;
            $sale->created_by           = \Auth::user()->creatorId();

            $sale->save();

            if($sale->save()){
                $status='sold';
                ProductIntake::where('serial_number', $request->serial_number)->update(array('status' => $status));
                $prod = ProductService::Where('name', $request->model_name)->first();

                ProductService::Where('name', $request->model_name)->update(array('quantity' => $prod->quantity - 1));
                
            }

            CustomField::saveData($sale, $request->customField);

            return redirect()->route('sale.index')->with('success', __('Product successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('delete product & service')) {
            $sale = Sale::find($id);

            $sale->delete();

            return redirect()->route('sale.index')->with('success', __('Product successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export()
    {

        $name = 'sales_' . date('Y-m-d i:h:s');

        $data = Excel::download(new SaleExport(), $name . '.xlsx');

        return $data;
    }
}
