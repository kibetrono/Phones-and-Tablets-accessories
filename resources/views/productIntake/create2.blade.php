{{ Form::open(array('url' => 'productintake')) }}
<div class="modal-body">
    <div class="row">
        {{-- <div class="col-md-4"></div>
        <div class="col-md-4"></div>
        <div class="col-md-4 text-center bg-success p-2" id="messageresponse">
            Product Saved
        </div>


    </div> --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('model_name[]', __('Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{-- {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }} --}}
                    {{ Form::select('model_name', $product_model_name,null, array('class' => 'form-control select2','id'=>'choices-multiple1','placeholder'=>'Select Model Name')) }}

                </div>
            </div>
        </div>

      {{-- start of product_service_id --}}
         <div  style="display:none" class="col-md-6">
            <div class="form-group">
                {{ Form::label('product_service_id', __('Product Service Id'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                        <input name="product_service_id" id="the_product_service_id" class="form-control" type="hidden" required readonly>

                </div>
            </div>
        </div>
        {{-- end of product_service_id --}}


        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('sale_price', __('Sale Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('sale_price', '', array('class' => 'form-control','id'=>'sale_price','required'=>'required','step'=>'0.01')) }}
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('retail_price', __('Recommended Retail Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('retail_price', '', array('class' => 'form-control','id'=>'retail_price','required'=>'required')) }}
                </div>
            </div>
        </div>


                {{-- start of supplier person --}}
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('supplier_person', __('Supplier'),['class'=>'form-label']) }}
                <div class="form-icon-user">

                    {{ Form::select('supplier_person', $supplier_person, null, array('class' => 'form-control select2','id'=>'choices-multiple2','required'=>'required','placeholder'=>'Select Supplier')) }}
                </div>
            </div>
        </div>
        {{-- end of supplier person --}}

        {{-- start of delivery person --}}
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('delivery_person', __('Delivery Person'),['class'=>'form-label']) }}
                <div class="form-icon-user">

                    {{ Form::select('delivery_person', $delivery_person_concat, null, array('class' => 'form-control select2','id'=>'choices-multiple3','required'=>'required','placeholder'=>"Select Delivery Person")) }}
                </div>
            </div>
        </div>
        {{-- end of delivery person --}}

        
                {{-- start of delivery_man_id--}}
        <div style="display:none" class="col-md-6">
            <div class="form-group">
                {{ Form::label('delivery_man_id', __('Delivery Man Id'),['class'=>'form-label']) }}
                <div class="form-icon-user">
                    <input name="delivery_man_id" id="delivery_man_id_id" class="form-control" type="hidden" required readonly>

                    {{-- {{ Form::select('delivery_person', $delivery_person, null, array('class' => 'form-control select2','id'=>'choices-multiple3','required'=>'required')) }} --}}
                </div>
            </div>
        </div>
        {{-- end of delivery_man_id --}}

        {{-- start of person receiving --}}
        <div style="display:none" class="col-md-6">
            <div class="form-group">
                {{ Form::label('receiving_person', __('Receiving Person'),['class'=>'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::hidden('receiving_person', $receiving_person,null, array('class' => 'form-control','id'=>'the_receiving_person','required'=>'required')) }}
                </div>
            </div>
        </div>
        {{-- end of person receiving--}}

        @if(!$customFields->isEmpty())
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif
    </div>

    <div class="row ">
         <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('imei_number', __('IMEI Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('imei_number', '', array('class' => 'form-control','id'=>'ime')) }}
                </div>
            </div>
        </div> 

        <div class="col-md-4">
            <div class="form-group">
                {{ Form::label('serial_number', __('Serial Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::text('serial_number', '', array('class' => 'form-control','id'=>'ser')) }}
                </div>
            </div>
        </div>

          <div class="col-md-4 mt-4">
            <div class="form-group">
               <div class="form-icon-user pt-2">
               
                <i id="addmore" class="fa fa-plus" style="border:1px solid green;padding:10px;background-color:green;color:white"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="row" id="appending_row">
        <div class="reew"></div>
    </div>
</div>
<div class="modal-footer">

    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}

<script>
    $(document).ready(function() {

        $(document).on('click','#addmore',function(e){
              var imei_num= $("#ime").val()
            var serial_num= $("#ser").val()

            if(imei_num != ''){
                $('#ime').prop('readonly', true);
            }
             if(serial_num != ''){
                $('#ser').prop('readonly', true);
            }

        })
        var myAdds='<div id="removed" class="row"><div class="col-md-4"><div class="form-group">{{ Form::label("imei_number", __("IMEI Number"),["class"=>"form-label"]) }}<span class="text-danger">*</span><div class="form-icon-user">{{ Form::number("imei_number_down", "", array("class" => "form-control","id"=>"imei_number_down","required"=>"required")) }}</div></div></div><div class="col-md-4"><div class="form-group">{{ Form::label("serial_number", __("Serial Number"),["class"=>"form-label"]) }}<span class="text-danger">*</span><div class="form-icon-user"><input type="text" name="serial_number_down" class="form-control" id="serial_number_down" required></div></div></div><div class="col-md-4 mt-3"><div class="input-group-append pt-3"><button class="btn btn-danger remove_field btn-sm" type="button">remove</button>&nbsp<button class="btn btn-success save_field btn-sm" type="button">save</button></div></div></div>'
        var max_fields = 2; 
        var x=1
        $(document).on('click','#addmore',function(e){
            $('.remove_field').off('click');
            e.preventDefault();
            if(x < max_fields){
                x++;
            $("#appending_row").append(myAdds)
            }
        })

         $(document).on('click','.remove_field',function(e){
            e.preventDefault(); 
            $('#removed').remove(); x--;

        })

        $(document).on('click','.save_field',function(e){
            e.preventDefault(); 
            var model_name= $("#choices-multiple1").val()
            var product_service_id= $("#the_product_service_id").val()
            var sale_price= $("#sale_price").val()
            var retail_price= $("#retail_price").val()
            var supplier_person= $("#choices-multiple2").val()
            var delivery_person= $("#choices-multiple3").val()
            var delivery_man_id= $("#delivery_man_id_id").val()
            var receiving_person= "<?php echo $receiving_person ?>"  // check on other end

            var imei_number_down= $("#imei_number_down").val()
            var serial_number_down= $("#serial_number_down").val()
            var _token=$("input[name=_token]").val()
                    
        
             $.ajax({
                url: "{{route('productintake.store')}}",
                type: "POST",
                data: {
                    model_name:model_name,
                    product_service_id:product_service_id,
                    sale_price:sale_price,
                    retail_price: retail_price,
                    supplier_person:supplier_person,
                    delivery_person:delivery_person,
                    delivery_man_id:delivery_man_id,
                    receiving_person:receiving_person,
                    imei_number_down:imei_number_down,
                    serial_number_down:serial_number_down,
                    _token:_token,
                },
                success: function(response) {
                    if (response) {
                        $("#messageresponse").append(response)
                        
                        console.log(response);
                    }
                },
                error: function(error) {
                        $("#messageresponse").append(response)
                }
            });
           
        })

        $(document).on('click','.save_field',function(){
            if($("#imei_number_down").val() == ''){
                $("#imei_number_down").css('border','1px solid red')

            }else if($("#serial_number_down").val() == ''){
                $("#serial_number_down").css('border','1px solid red')
            }
            else{
                $('#removed').remove(); x--;
            }

        })

        $(document).on('change', '#choices-multiple3', function() {
            let prod_name = $(this).val();
            $.ajax({
                url: 'getdelivery_man_id',
                type: 'get',
                dataType: 'json',
                data: {
                    'delivery_person': prod_name
                },
                success: function(response) {
                    if (response != null) {
                        $('#delivery_man_id_id').val(response.id);
                    }
                },
                error: function() {}
            });
        })
         $(document).on('change', '#choices-multiple1', function() {
            let prod_name = $(this).val();
            // alert(prod_name);
            $.ajax({
                url: 'getproductmodelnameid',
                type: 'get',
                dataType: 'json',
                data: {
                    'model_name': prod_name
                },
                success: function(response) {
                    if (response != null) {
                        $('#the_product_service_id').val(response.id);
                    }
                },
                error: function() {}
            });
        })

    })
</script>


