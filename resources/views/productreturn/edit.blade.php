

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
            {{ Form::label('Product', __('Product'),['class'=>'form-label']) }}<br>
            {{ Form::select('model_name[]', $product_model_name,null, array('class' => 'form-control select2','id'=>'choices-multiple1')) }}

            {{-- {{ Form::text('model_name[]',null, array('class' => 'form-control','required'=>'required','id'=>'main_input')) }} --}}

        </div>

        {{-- <div style="display: none" id="hiddenproperty" class="form-group col-md-6">
            {{ Form::label('Product', __('Phone Model'),['class'=>'form-label']) }}<br>
            {{ Form::select('model_name[]', $product_model_name,null, array('class' => 'form-control select2','id'=>'choices-multiple1')) }}

        </div> --}}

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('IMEI Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::text('imei_number',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Serial Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
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
                {{ Form::label('name', __('Recommended Retail Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('retail_price', null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Invoice Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::text('invoice_number',null, array('class' => 'form-control')) }}
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
