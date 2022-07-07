{{Form::open(array('url'=>'deliveryman','method'=>'post'))}}
<div class="modal-body">

    <h5 class="sub-title">{{__('Basic Info')}}</h5>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('first_name',__('First Name'),array('class'=>'form-label')) }}
                <div class="form-icon-user">
                    {{Form::text('first_name',null,array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
        </div>
                <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('last_name',__('Last Name'),array('class'=>'form-label')) }}
                <div class="form-icon-user">
                    {{Form::text('last_name',null,array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('contact',__('Contact'),['class'=>'form-label'])}}
                <div class="form-icon-user">
                    {{Form::text('contact',null,array('class'=>'form-control','required'=>'required'))}}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                <div class="form-icon-user">
                    {{Form::text('email',null,array('class'=>'form-control'))}}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('password',__('ID Number'),['class'=>'form-label'])}}
                <div class="form-icon-user">
                    <input name="id_number" type="number" class="form-control" required minlength="8">
                    {{-- {{Form::number('id_number',array('class'=>'form-control','required'=>'required','minlength'=>"8"))}} --}}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('password',__('Password'),['class'=>'form-label'])}}
                <div class="form-icon-user">
                    {{Form::password('password',array('class'=>'form-control','required'=>'required','minlength'=>"6"))}}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            <div class="form-group">
                {{Form::label('tax_number',__('Tax Number'),['class'=>'form-label'])}}
                <div class="form-icon-user">
                    {{Form::text('tax_number',null,array('class'=>'form-control'))}}
                </div>
            </div>
        </div>
        @if(!$customFields->isEmpty())
            <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif
    </div>    

</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{Form::close()}}
