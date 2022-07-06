

<script>
    // $(document).on('click', '#main_input', function () {
    //     $("#hiddenproperty").css("display", "block");
    //     document.getElementById('main_input').disabled=true;
   
    // })

</script>


{{ Form::model($productIntake, array('route' => array('productintake.update', $productIntake->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">

        <div  class="form-group col-md-6">
            {{ Form::label('model_name[]', __('Product'),['class'=>'form-label']) }}<br>
            {{ Form::select('model_name', $product_model_name,null, array('class' => 'form-control select2','id'=>'choices-multiple1')) }}

        </div>

         {{ Form::hidden('product_service_id',null, array('class' => 'form-control','required'=>'required','id'=>'product_service_id_input')) }}
        <input name="delivery_man_id" type="hidden" value="{{$productIntake->delivery_man_id}}" id="myownselect">
        <input name="vender_id" type="hidden" value="{{$productIntake->vender_id}}" id="myownselect2">

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('imei_number', __('IMEI Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::text('imei_number',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('serial_number', __('Serial Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::text('serial_number',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>

        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('sale_price', __('Sale Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('sale_price', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('retail_price', __('Retail Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('retail_price', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Recommended Retail Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('retail_price', null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div>


         <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('supplier_person', __('Supplier'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::select('supplier_person', $the_supplier_person,null, array('class' => 'form-control select2','id'=>'choices-multiple2')) }}
                </div>
            </div>            
        </div>

        {{-- <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('delivery_person', __('Delivery Person Contact'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::select('delivery_person', $the_delivery_person,null, array('class' => 'form-control select2','id'=>'choices-multiple3')) }}

                </div>
            </div>            
        </div> --}}

         {{-- start of delivery person --}}
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('delivery_person', __('Delivery Person'),['class'=>'form-label']) }}
                <div class="form-icon-user">

                    {{ Form::select('delivery_person', $my_delivery_person, null, array('class' => 'form-control select2','id'=>'choices-multiple3','required'=>'required')) }}
                </div>
            </div>
        </div>
        {{-- end of delivery person --}}

        <div style="display:none" class="col-md-6">
            <div class="form-group">
                {{ Form::label('receiving_person', __('Receiving Person'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::hidden('receiving_person',null, array('class' => 'form-control')) }}
                </div>
            </div>            
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


<script>
    $(document).ready(function(){
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
                        $('#product_service_id_input').val(response.id);
                    }
                },
                error: function() {}
            });
        })

        $(document).on('change', '#choices-multiple2', function() {
            let prod_name = $(this).val();
            $.ajax({
                url: 'getvender_id',
                type: 'get',
                dataType: 'json',
                data: {
                    'supplier_person': prod_name
                },
                success: function(response) {
                    if (response != null) {
                        $('#myownselect2').val(response.id);
                    }
                },
                error: function() {}
            });
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
                        $('#myownselect').val(response.id);
                    }
                },
                error: function() {}
            });
        })
    })
</script>