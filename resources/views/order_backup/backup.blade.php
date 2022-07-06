{{ Form::open(array('url' => 'order')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('model_name[]', __('Product Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{-- {{ Form::select('model_name', $product_model_name,null, array('class' => 'form-control select2','id'=>'choices-multiple2','placeholder'=>'Select Product  Name','required'=>'required')) }} --}}
                    <select class="form-control" name="model_name" id="" required>
                        <option disabled selected>Select Product  Name</option>
                        @foreach ($product_model_name as $product)
                        <option value="{{$product->model_name}}">{{$product->model_name}}</option>
                            
                        @endforeach
                    </select>
                </div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('imei_number', __('IMEI Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::select('imei_number', $product_imei_number,null, array('class' => 'form-control select2','id'=>'selectimei', 'required'=>'required','placeholder'=>'Select IMEI Number')) }}
                </div>
            </div>
        </div> 

        {{-- <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('quantity', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div> --}}

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
                {{ Form::label('payment_type', __('Payment Type'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{-- {{ Form::select('payment_type',$payment_types,null, array('class' => 'form-control','required'=>'required','placeholder'=>'Select Payment Method')) }} --}}
                    <select name="payment_type" id="" class="form-control">
                        <option value="">Select Payment Method</option>
                        @foreach ($payment_types as $payment_type)
                            <option value="{{$payment_type}}">{{$payment_type}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('invoice_number', __('Invoice Number'),['class'=>'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::number('invoice_number', '', array('class' => 'form-control')) }}
                </div>
            </div>
        </div>

                {{-- start of supplier person --}}
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('customer_name', __('Customers Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">

                    {{ Form::select('customer_name', $customer_name, null, array('class' => 'form-control select2','id'=>'choices-multiple4','required'=>'required','placeholder'=>'Select Customer')) }}
                </div>
            </div>
        </div>
        {{-- end of supplier person --}}


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


