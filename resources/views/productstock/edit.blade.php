{{ Form::model($productService, array('route' => array('productstock.update', $productService->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">

        <div class="form-group col-md-6">
            {{ Form::label('Product', __('Product'),['class'=>'form-label']) }}<br>
            {{$productService->name}}

        </div>
        <div class="form-group col-md-6">
            {{ Form::label('Product', __('SKU'),['class'=>'form-label']) }}<br>
            {{$productService->sku}}

        </div>
 {{-- <div class="form-group col-md-4">
            {{ Form::label('Supplier', __('Supplier'),['class'=>'form-label']) }}<br>
            {{$productService->sku}}

        </div> --}}

        <div class="form-group quantity">
            <div class="d-flex radio-check ">
                <div class="form-check form-check-inline form-group col-md-6">
                    <input type="radio" id="plus_quantity" value="Add" name="quantity_type" class="form-check-input" checked="checked">
                    <label class="form-check-label" for="plus_quantity">{{__('Add Quantity')}}</label>
                </div>
                <div class="form-check form-check-inline form-group col-md-6">
                    <input type="radio" id="minus_quantity" value="Less" name="quantity_type" class="form-check-input">
                    <label class="form-check-label" for="minus_quantity">{{__('Less Quantity')}}</label>
                </div>
            </div>
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{ Form::number('quantity',"", array('class' => 'form-control','required'=>'required')) }}
        </div>

        <div class="form-group col-md-6">
        <p class="btn btn-primary status_change" data-url="{{route('product.status.change',$productService->id)}}">Sync Product</p>
        </div>
        <div class="form-group col-md-6" id="msg" style="display: none">
            {{-- <h5 class="pt-3" style="color:green">Status changed</h5> --}}
            <input type="text" style="border: unset" class="form-control" id="totalnum">
        </div>


 
    </div>


</div>
<div class="modal-footer">

    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


{{-- 
<select class="form-control status_change select2 mb-4" name="status" id="" data-url="{{route('product.status.change',$productService->id)}}">
@foreach ($status as $key=>$value)
    <option value="{{$key}}" {{($productService->status==$key)?'selected':''}}> {{$value}}</option>

@endforeach
</select> --}}
{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}

<script>

    $(document).ready(function(){
        var id="<?php echo $productService->id ?>"

        // alert(id)
        $.ajax({

            url:'getsupplier',
            data:{},
            dataType,
            success:function(response){
                if(response != null){
                    console.log("Response",response);
                }
            },error:function(error){
                console.log("The Error",error);
            }
        });
    })
    $(document).on('click', '.status_change', function ()
     {
            var status ='<?php echo $stock_status ?>';
                
            var url = $(this).data('url');

            $.ajax({
                url: url + '?status=' + status,
                
                type: 'GET',
                cache: false,
                success: function (data) {
                    // $('#msg').show().delay(1000).fadeOut(2000)
                },error: function(errs){


                },
            });
        });

    $(document).on('click','.status_change',function(){
        $.ajax({
                url: 'count',
                type: 'GET',
                cache:false,
                success: function(response) {
                    if (response != null) {
                        // console.log("Response",response);
                    // $('#msg').show().delay(1000).fadeOut(5000)
                    $('#msg').css('display','block')

                        $('#totalnum').val(response[0] + " products in stock.");

                        // console.log(response[0]);
                    }
                },
                error: function() {}
            });    })


</script>