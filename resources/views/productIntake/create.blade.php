{{ Form::open(array('url' => 'productintake')) }}
<div class="modal-body">
    <div class="row">
         {{-- start of softwaresKe --}}
         <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Phone Model'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::text('model_name', '', array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div> 

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Quantity Delivered'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    {{ Form::number('quantity_number', '', array('class' => 'form-control','required'=>'required')) }}
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
     
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


