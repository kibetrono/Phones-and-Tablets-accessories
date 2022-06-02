{{ Form::model($payment, array('route' => array('payment.update', $payment->id), 'method' => 'PUT','enctype' => 'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
            <div class="form-icon-user">
                {{Form::date('date',null,array('class'=>'form-control','required'=>'required'))}}

            </div>
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
            <div class="form-icon-user">
                {{ Form::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
            </div>
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('account_id', __('Account'),['class'=>'form-label']) }}
            {{ Form::select('account_id',$accounts,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('vender_id', __('Vendor'),['class'=>'form-label']) }}
            {{ Form::select('vender_id', $venders,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Form::textarea('description', null, array('class' => 'form-control','rows'=>3)) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('category_id', __('Category'),['class'=>'form-label']) }}
            {{ Form::select('category_id', $categories,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
            <div class="form-icon-user">
                {{ Form::text('reference', null, array('class' => 'form-control')) }}
            </div>
        </div>
{{--        <div class="col-sm-12 col-md-12">--}}
{{--            <div class="form-group">--}}
{{--                {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}--}}
{{--                <input type="file" name="add_receipt" id="image" class="custom-input-file" accept="image/*, .txt, .rar, .zip" >--}}
{{--                <label for="image">--}}
{{--                    <i class="fa fa-upload"></i>--}}
{{--                    <span>Choose a file…</span>--}}
{{--                </label>--}}
{{--            </div>--}}
{{--        </div>--}}

        <div class="col-md-12">
            {{Form::label('add_receipt',__('Payment Receipt'),['class'=>'form-label'])}}
            <div class="choose-file form-group">
                <label for="image" class="form-label">
                    <input type="file" class="form-control" name="add_receipt" id="image" data-filename="upload_file" required>
                </label>
                <p class="upload_file"></p>
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
