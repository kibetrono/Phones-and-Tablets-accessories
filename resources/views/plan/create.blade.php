{{ Form::open(array('url' => 'plans', 'enctype' => "multipart/form-data")) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{Form::label('name',__('Name'),['class'=>'form-label'])}}
            {{Form::text('name',null,array('class'=>'form-control font-style','placeholder'=>__('Enter Plan Name'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('price',__('Price'),['class'=>'form-label'])}}
            {{Form::number('price',null,array('class'=>'form-control','placeholder'=>__('Enter Plan Price')))}}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('duration', __('Duration'),['class'=>'form-label']) }}
            {!! Form::select('duration', $arrDuration, null,array('class' => 'form-control select','required'=>'required')) !!}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('max_users',__('Maximum Users'),['class'=>'form-label'])}}
            {{Form::number('max_users',null,array('class'=>'form-control','required'=>'required'))}}
            <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
        </div>
        <div class="form-group col-md-6">
            {{Form::label('max_customers',__('Maximum Customers'),['class'=>'form-label'])}}
            {{Form::number('max_customers',null,array('class'=>'form-control','required'=>'required'))}}
            <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
        </div>
        <div class="form-group col-md-6">
            {{Form::label('max_venders',__('Maximum Venders'),['class'=>'form-label'])}}
            {{Form::number('max_venders',null,array('class'=>'form-control','required'=>'required'))}}
            <span class="small">{{__('Note: "-1" for Unlimited')}}</span>
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
        </div>
    
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
