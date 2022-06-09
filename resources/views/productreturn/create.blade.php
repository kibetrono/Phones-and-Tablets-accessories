{{ Form::open(array('url' => 'productreturn')) }}

<div class="modal-body">
    <div class="row">
        
        {{-- <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::select('model_name', $product_model_name,null, array('class' => 'form-control select2','id'=>'choices-multiple1','required'=>'required')) }}

                </div>
            </div>
        </div> --}}

        {{-- <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('IMEI Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::select('imei_number',$product_imei_no,null, array('class' => 'form-control select2','id'=>'choices-multiple3','required'=>'required')) }}
                </div>
            </div>
        </div>  --}}

        <div class="col-md-3"></div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Serial Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::select('serial_number', $product_serial_no,null, array('class' => 'form-control select2','id'=>'choices-multiple4','required'=>'required')) }}
                </div>
            </div>
        </div>

        <div class="col-md-3"></div>

        {{-- <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Suppliers\'s Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::select('suppliers_name', $suppliers_name,null, array('class' => 'form-control select2','id'=>'choices-multiple5','required'=>'required')) }}
                </div>
            </div>
        </div> --}}

        {{-- <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Delivery Person'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::select('deliveryPersons_name', $deliveryPersons_name,null, array('class' => 'form-control select2','id'=>'choices-multiple6','required'=>'required')) }}
                </div>
            </div>
        </div> --}}

        {{-- <div style="display:none" class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Person Receiving'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::hidden('person_receiving_in_shop', $person_receiving_in_shop, array('class' => 'form-control','required'=>'required')) }}

                </div>
            </div>
        </div> --}}
{{-- 
        <div style="display:none" class="col-md-6">
            <div class="form-group">
                 {{ Form::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::hidden('quantity',1, array('class' => 'form-control','required'=>'required')) }}

                </div>
            </div>
        </div> --}}
     
{{-- 
        @if(!$customFields->isEmpty())
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif --}}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Submit')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


