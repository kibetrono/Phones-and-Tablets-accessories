<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Vender;
use App\Models\CustomField;
use Illuminate\Http\Request;
use App\Models\ProductIntake;
use App\Models\ProductService;
use App\Models\ProductServiceUnit;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductServiceExport;
use App\Imports\ProductServiceImport;
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
        $productIntakes=ProductIntake::all();
        //  return view('productservice.index', compact('productServices'));
    
            if (\Auth::user()->can('manage product & service')) {
      
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

            return view('productIntake.create');
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
    public function store(ProductIntakeStoreRequest $productIntakeStoreRequest)
    {
        //
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
    public function edit(ProductIntake $productIntake)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductIntake  $productIntake
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductIntake $productIntake)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductIntake  $productIntake
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductIntake $productIntake)
    {
        //
    }
}
