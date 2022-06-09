{{ Form::open(array('url' => 'productintake')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('model_name[]', __('Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{-- {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }} --}}
                    {{ Form::select('model_name[]', $product_model_name,null, array('class' => 'form-control select2','id'=>'choices-multiple1')) }}

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('imei_number', __('IMEI Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('imei_number', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div> 

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('serial_number', __('Serial Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
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
                {{ Form::label('retail_price', __('Recommended Retail Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('retail_price', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('invoice_number', __('Invoice Number (optional)'),['class'=>'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::number('invoice_number', '', array('class' => 'form-control')) }}
                </div>
            </div>
        </div>

                {{-- start of supplier person --}}
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('supplier_person', __('Supplier'),['class'=>'form-label']) }}
                <div class="form-icon-user">

                    {{ Form::select('supplier_person', $supplier_person, null, array('class' => 'form-control select2','id'=>'choices-multiple2','required'=>'required')) }}
                </div>
            </div>
        </div>
        {{-- end of supplier person --}}

        {{-- start of delivery person --}}
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('delivery_person', __('Delivery Person Contacts'),['class'=>'form-label']) }}
                <div class="form-icon-user">

                    {{ Form::select('delivery_person', $delivery_person, null, array('class' => 'form-control select2','id'=>'choices-multiple3','required'=>'required')) }}
                </div>
            </div>
        </div>
        {{-- end of delivery person --}}

        {{-- start of person receiving --}}
        <div style="display:none" class="col-md-6">
            <div class="form-group">
                {{ Form::label('receiving_person', __('Receiving Person'),['class'=>'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::hidden('receiving_person', $receiving_person,null, array('class' => 'form-control','required'=>'required')) }}
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
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


