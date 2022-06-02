{{ Form::open(array('url' => 'productintake')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{-- {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }} --}}
                    {{ Form::select('model_name[]', $product_model_name,null, array('class' => 'form-control select2','id'=>'choices-multiple1')) }}

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('sku', __('SKU'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::text('sku', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('IMEI Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('imei_number', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div> 

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Serial Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::text('serial_number', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('sale_price', __('Sale Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('sale_price', '', array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Recommended Retail Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('retail_price', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Invoice Number (optional)'),['class'=>'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::number('invoice_number', '', array('class' => 'form-control')) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <div class="btn-box">
                    <label class="d-block form-label">{{__('Type')}}</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" id="customRadio5" name="type" value="product" checked="checked" onclick="hide_show(this)">
                                <label class="custom-control-label form-label" for="customRadio5">{{__('Product')}}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" id="customRadio6" name="type" value="service" onclick="hide_show(this)">
                                <label class="custom-control-label form-label" for="customRadio6">{{__('Service')}}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!$customFields->isEmpty())
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


