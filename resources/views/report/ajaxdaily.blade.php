@extends('layouts.admin')
@section('page-title')
    {{__('Daily Report')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Product Daily Report')}}</li>
@endsection

@section('content')
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    </head>
    <body>
            <hr>
        <div class="row">

            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('date_range', __('Date Range'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        <select name="date_range_name" id="the_date_range" class="form-control">
                            @foreach ($date_range as $date_key=>$date)
                            <option value="{{$date_key}}">{{$date}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            <div class="dates" style="display:none" id="the_custom_range">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                            <div class="form-icon-user">
                                <input class="form-control" name="start_date" id="the_start_date" type="date">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2"></div>

                    <div class="col-md-5">
                        <div class="form-group">
                            {{ Form::label('end_date', __('End Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                            <div class="form-icon-user">
                                <input class="form-control" name="end_date" id="the_end_date" type="date">
                            </div>
                        </div>
                    </div>

                 </div>
            </div>

            </div>

            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('model_name', __('Product'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        {{Form::select('product_name',$productselect,null,array('class'=>'form-control select2','id'=>'the_product_select','placeholder'=>'Select Product'))}}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('the_supplier', __('Supplier'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        {{Form::select('the_supplier',$supplierselect,null,array('class'=>'form-control select2','id'=>'the_supplier_select','placeholder'=>'Select Supplier'))}}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('the_status', __('Stock Status'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        <select name="the_status" id="the_status_select" class="form-control">
                            @foreach ($the_status as $key=>$status)
                            <option value="{{$status}}">{{$status}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

             <div class="col-md-4">
                <div class="form-group">
                    <div class="form-icon-user mt-4">
                        <button id="filterbtn" class="btn btn-primary">Filter</button>
                        <button id="resetbtn" class="btn btn-primary">Reset</button>
                     </div>
                </div>
            </div>
            
             <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('the_status', __('Export Options'),['class'=>'form-label']) }}
                    <div class="form-icon-user">
                        <select name="the_export" id="export_type" class="form-control">
                            <option value="" selected disabled style="padding:30px">Export Options</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                </div>
            </div> 

        </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id='the_example'>
                            <thead>
                            <tr>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Product Name')}}</th>
                                <th>{{__('Sale Price')}}</th>
                                <th>{{__('Supplier Name')}}</th>
                                <th>{{__('Status')}}</th>
                              
                            </tr>
                            </thead>
                           <tbody>
                            
                           </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery.dataTables.min.js" type="text/javascript"></script>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"  defer type="text/javascript"></script>
<script>
   $(document).ready(function () {
    $('#the_example').DataTable({
                ajax: 'fetchallreports',

                "columns":[
                    {data:"created_at"},
                    {data:"model_name"},
                    {data:"sale_price"},
                    {data:"supplier_person"},
                    {data:"status"}
                ]

    });
});

</script>
    </body>
    </html>

       
@endsection



