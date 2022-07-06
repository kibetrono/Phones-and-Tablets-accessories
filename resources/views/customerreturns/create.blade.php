<form method="POST" action="{{ route('customerreturns.store') }}">
                        @csrf
 <input name="product_id" id="status_id" class="form-control" type="hidden" required readonly>              

<div class="modal-body">
    <div class="row">        

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('serial_number', __('Serial Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                  {{ Form::select('serial_number', array_merge(['' => "Select Serial Number"] + $product_serial_number),null, array('class' => 'form-control select2','id'=>'choices-multiple1','required'=>'required')) }}

                </div>
            </div>
        </div>         

         <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('model_name', __('Model Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                   <input name="model_name" id="model_name_id" class="form-control" type="text" required readonly>              
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('imei_number', __('IMEI Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                   <input name="imei_number" id="imei_number_id" class="form-control" type="number" required readonly>              
                </div>
            </div>
        </div>

        {{-- <div style="display:none" class="col-md-6">
            <div class="form-group">
                {{ Form::label('quantity_delivered', __('Quantity Delivered'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                   <input name="quantity_delivered" value="1" class="form-control" type="hidden" required readonly>              
                </div>
            </div>
        </div> --}}

 
        
                {{-- start of customers name --}}
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('returning_customer', __('Customers Name'),['class'=>'form-label']) }}
                <div class="form-icon-user">
                   {{ Form::select('returning_customer', array_merge(['' => "Select Customer"] +$returning_customer),null, array('class' => 'form-control select2','id'=>'choices-multiple2','required'=>'required')) }}

                </div>
            </div>
        </div>
        {{-- end of customers name--}}

        {{-- start of person receiving --}}
        {{-- <div style="display:none" class="col-md-6">
            <div class="form-group">
                {{ Form::label('receiving_person', __('Receiving Person'),['class'=>'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::hidden('receiving_person', $receiving_person,null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
        </div> --}}
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
    <input type="submit" value="{{__('Submit')}}" class="btn  btn-primary">
</div>
</form>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>

 $(document).ready(function(){
    $(document).on('change','#choices-multiple1',function(){
        let prod_name = $(this).val();
        $.ajax({ 
            url:'getproductdata',type: 'get',dataType: 'json',
            data:{'serial_number':prod_name},
            success: function(response) {if (response != null) {$('#model_name_id').val(response.model_name);}},error:function(){ }
        });      
    })

     $(document).on('change','#choices-multiple1',function(){
        let prod_name = $(this).val();
        $.ajax({ 
            url:'getproductdata',type: 'get',dataType: 'json',
            data:{'serial_number':prod_name},
            success: function(response) {if (response != null) {$('#imei_number_id').val(response.imei_number);}},error:function(){ }
        });      
    })

    $(document).on('change','#choices-multiple1',function(){
        let prod_name = $(this).val();
        $.ajax({ 
            url:'getproductdata',type: 'get',dataType: 'json',
            data:{'serial_number':prod_name},
            success: function(response) {if (response != null) {
            }
            },error:function(){ }
        });      
    })

    $(document).on('change','#choices-multiple1',function(){
        var input_value= $(this).val()

        $.ajax({
            url:'getproductdata',
            type:'get',
            dataType:'json',
            data:{'serial_number':input_value},
            success: function(response){
                if(response !=null){
                    $('#status_id').val(response.id)
                }
            },error:function(){
                console.log(error);
            }

        })

     })

});
   
   
</script> 
