@extends('layouts.admin')
@section('page-title')
    {{__('Daily Report')}}
@endsection
{{-- @section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Product Daily Report')}}</li>
@endsection --}}

@section('content')
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
                        <button id="resetbtn" type="reset" class="btn btn-primary">Reset</button>
                     </div>
                </div>
            </div>

            <div class="col md-2"></div>
            
             <div class="col-md-2" >
                <div class="form-group">
                    <div class="form-icon-user mt-4" >
                        <select name="the_export" style="background-color:#ddd;border-radius:5px" id="export_type" class="form-control">
                            <option value="" selected disabled style="padding:30px;">Export Options</option><br><br><br>
                            <option style="background-color:white;border-radius:5px;font-size:17px;color:rgb(0, 195, 255)"  value="csv">CSV</option><br><br>
                            <option style="background-color:white;border-radius:5px;font-size:17px;color:rgb(0, 195, 255)" value="pdf">PDF</option><br><br>
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
                        <table class="table datatable" id='myuserTable'>
                            <thead>
                            <tr>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Product Name')}}</th>
                                <th>{{__('Sale Price')}}</th>
                                <th>{{__('Supplier Name')}}</th>
                                <th>{{__('Status')}}</th>
                              
                            </tr>
                            </thead>
                            <tbody >
                                
                                @foreach ($allproducts as $product)
                                    <tr>
                                        <td>{{$product['created_at']->format('Y-m-d')}}</td>
                                        <td>{{$product['model_name']}}</td>
                                        <td>Kshs. {{$product->sale_price}}</td>
                                        <td><a href="{{ route('supplier.show', \Crypt::encrypt($product['vender_id'])) }}">{{$product->supplier_person}}</a></td>
                                        <td>{{$product['status']}}</td>
                                    </tr>
                                    
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<style>
    .dataTables_length label{
padding: 25px 25px
    }
    #myuserTable_filter label{
padding: 25px 20px
    }
   
</style>
       
@endsection
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
{{-- <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"  defer type="text/javascript"></script> --}}

<script  src="{{ asset('assets/js/plugins/daily-report.js') }}" defer type="text/javascript"></script>

<script>
    // start of filter
    $(document).ready(function(){

        $(document).on('change','#the_date_range',function(){
            
            var date_range= $("#the_date_range").val()

            if(date_range == 5){
             $('#the_custom_range').show()
             var start_date =$('#the_start_date').val()
             var end_date =$('#the_end_date').val()
            }else{
             $('#the_custom_range').hide()
            }

        })



        $(document).on('click','#filterbtn',function(){

            $.fn.dataTable.ext.errMode = 'none';
            var start_date =$('#the_start_date').val()
            var end_date =$('#the_end_date').val()
            var normal_date =$('#the_end_date').val()
            var date_range= $("#the_date_range").val()
            var product_select= $("#the_product_select").val()
            var supplier_select= $("#the_supplier_select").val()
            var status_select= $("#the_status_select").val()

            $.ajax({
                
                url:'fetchallreports',
                data:{
                    'date_range_name':date_range,
                    'start_date_name':start_date,
                    'end_date_name':end_date,
                    'product_name':product_select,
                    'the_supplier':supplier_select,
                    'the_status':status_select,
                },
                type:"GET",
                dataType:'json',
                success:function(response){
                    if(response !=null){
                        $(".dataTable-top").remove()
                        $(".dataTable-bottom").remove()
                        $('#myuserTable tbody').empty()
                        $('#myuserTable_filter').remove()
                        // id="myuserTable_filter"
                        var len = 0;
                        //  $('#myuserTable tbody').empty(); // Empty <tbody>
                            data = JSON.parse(response);
                            data2=data['data']
                            len = data2.length

                            console.log('The Response',len);

                                
                    if(len>0){
                        $('#myuserTable_filter').remove()

                        total=0;
                         for (var i = 0; i < data2.length; i++) {  
                        $('#myuserTable_filter').remove()

                                    var sale_price = (data2[i].sale_price).toLocaleString();
                                    total +=data2[i].sale_price;
                          
                                    }
                               if ( ! $.fn.DataTable.isDataTable( '#myuserTable' ) ) {


                    $('#myuserTable').dataTable({
                            data: data2,                
                            "columns":[
                                { data: 'created_at', 
                                render: function (data, type, row) {                
                                    d= new Date (Date.parse(data))
                                    the_date=d.getFullYear()+'-'+ (d.getMonth() + 1)+'-'+d.getDate()
                                    return (the_date);
                                    } 
                                },
                                {data:"model_name",},
                                {data:"sale_price"},
                                {data:"supplier_person",
                                        render: function ( data, type, row, meta ) {
                                        url = "{{ route('supplier.show', ':id') }}";
                                            url = url.replace(':id', row.vender_id);  
                                    return '<a href="'+url+'">'+data+'</a>';
                                }  
                                
                                },
                                {data:"status"}

                                    ]
                                });

                            }else{
                                    $('#myuserTable').dataTable().fnClearTable();
                                        $('#myuserTable').dataTable().fnDestroy();

                        $('#myuserTable').dataTable({
                            
                            data: data2,                
                            "columns":[
                                { data: 'created_at', 
                                render: function (data, type, row) {                
                                    d= new Date (Date.parse(data))
                                    the_date=d.getFullYear()+'-'+ (d.getMonth() + 1)+'-'+d.getDate()
                                    return (the_date);
                                    } 
                                },
                                {data:"model_name",},
                                {data:"sale_price"},
                                {data:"supplier_person",
                                        render: function ( data, type, row, meta ) {
                                        url = "{{ route('supplier.show', ':id') }}";
                                            url = url.replace(':id', row.vender_id);  
                                    return '<a href="'+url+'">'+data+'</a>';
                                }  
                                
                                },
                                {data:"status"}

                                    ]
                                });
                            }


                                 var price=total.toLocaleString();

                                var the_data="<tr id='the_row'><td></td><td></td><td></td><td></td><td><span style='color:#0CA49D'>Total Price:</span> Kshs."+ price +" <br><br> <span style='color:#0CA49D'>Total Quantity:</span>"+ len +"</td></tr>"
                                
                                $("#myuserTable tbody").append(the_data);

                  
                                $(document).on('click','.paginate_button',function(){
                                            var className = $(this).attr('class').match(/\S*current\b/i);
                                           if(className){
                                            if($("#the_row").is(":visible")){
                                        } else{

                                $("#myuserTable tbody").append(the_data);
                                    }

                                           }else{
                                $("#myuserTable tbody").append(the_data);

                                           }
                            
                                    })
                                $(document).on('click','.dataTables_length',function(){
                        if($("#the_row").is(":visible")){
                                        } else{

                                $("#myuserTable tbody").append(the_data);
                                    }                    
                                
                                })

                        //          $(document).on('click','#myuserTable_filter',function(){
                        // if($("#the_row").is(":visible")){
                        //                 } else{

                        //         $("#myuserTable tbody").append(the_data);
                        //             }                    
                                
                        //         })

                                

                        


                    }
                    else{

                        var tr_str = "<tr>" +"<td align='center' colspan='4'>No records found.</td>" +"</tr>";

                            $("#myuserTable tbody").append(tr_str);

                            
                    }
               
         
                }
                },
                error:function(error){
                }
            })

        })

        $(document).on('change','#export_type',function(){

            var the_value=$(this).val();

            var start_date =$('#the_start_date').val()
            var end_date =$('#the_end_date').val()
            var normal_date =$('#the_end_date').val()
            var date_range= $("#the_date_range").val()
            var product_select= $("#the_product_select").val()
            var supplier_select= $("#the_supplier_select").val()
            var status_select= $("#the_status_select").val()
            if(the_value == 'csv'){
            $.ajax({

                url:'exportdata',
                type:'GET',
                data:{
                    'date_range_name':date_range,
                    'start_date_name':start_date,
                    'end_date_name':end_date,
                    'product_name':product_select,
                    'the_supplier':supplier_select,
                    'the_status':status_select,
                },
                success:function(response){
                },error:function(error){
                }
            })
         }
         else{
            $.ajax({
                url:'generate-pdf',
                type:'GET',
                data:{
                    'date_range_name':date_range,
                    'start_date_name':start_date,
                    'end_date_name':end_date,
                    'product_name':product_select,
                    'the_supplier':supplier_select,
                    'the_status':status_select,
                },
                success:function(response){
                },error:function(error){
                }
            })
         }
        })

        $(document).on('click','#resetbtn',function(){
$('#the_product_select').resetForm()
       })
    })
    // end of filter

</script>

