<form method="POST" action="{{ route('productreturn.store') }}">
    @csrf
    <div class="modal-body">
        <div class="row">

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('serial_number', __('Serial Number'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        {{ Form::select('serial_number',$product_serial_number,null, array('class' => 'form-control select2','id'=>'choices-multiple1','required'=>'required','placeholder'=>'Select Serial Number')) }}
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


            <div style="display:none" class="col-md-6">
                <div class="form-group">
                    {{ Form::label('sale_price', __('Sale Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        <input name="sale_price" id="sale_price_id" class="form-control" type="hidden" required readonly>
                    </div>
                </div>
            </div>

            <div style="display:none" class="col-md-6">
                <div class="form-group">
                    {{ Form::label('retail_price', __('Retail Price'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        <input name="retail_price" id="retail_price_id" class="form-control" type="hidden" required readonly>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name', __('Suppliers\'s Name'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        <input name="supplier_person" id="supplier_person_id" class="form-control" type="text" required readonly>
                    </div>
                </div>
            </div>

            {{-- <div  class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name', __('Delivery Person'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    <div class="form-icon-user">
                        <input name="delivery_person" id="delivery_person_id" class="form-control" type="number" required readonly>
                    </div>
                </div>
            </div> --}}

            {{-- returning person --}}
        <div class="col-md-6">
             <div class="form-group">
                {{ Form::label('name', __(' Person Returning '),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                    <select name="returning_person" id="myownselect" class="form-control">
                         <option value=""selected disabled>Select Delivery Person</option>
                         @foreach ($delivery_person_concat as $item)
                             <option value="{{$item}}">{{$item}}</option>
                         @endforeach
                    </select>
                </div>
             </div>
         </div>
            {{-- end of returning person --}}





     {{-- <div class="col-md-6">
             <div class="form-group">
                {{ Form::label('name', __(' Person2 '),['class'=>'form-label']) }}<span class="text-danger">*</span>
                <div class="form-icon-user">
                   <select name="returning_person_id" id="myownselect2">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                    @endforeach
                   </select>
                </div>
             </div>
         </div> --}}

         <input id="myownselect2" name="returning_person_id" type="hidden" class="form-control">





            {{-- start of person receiving --}}
            <div style="display:none" class="col-md-6">
                <div class="form-group">
                    {{ Form::label('receiving_person', __('Receiving Person'),['class'=>'form-label']) }}
                    <div class="form-icon-user">
                        <input name="receiving_person" id="receiving_person_id" class="form-control" type="hidden" required readonly>

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
        <input type="submit" value="{{__('Submit')}}" class="btn  btn-primary">
    </div>
</form>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

    $(document).ready(function() {
        $(document).on('change', '#choices-multiple1', function() {
            let prod_name = $(this).val();
            $.ajax({
                url: 'getproductdetails',
                type: 'get',
                dataType: 'json',
                data: {
                    'serial_number': prod_name
                },
                success: function(response) {
                    if (response != null) {
                        $('#model_name_id').val(response.model_name);
                        $('#imei_number_id').val(response.imei_number);
                        $('#sale_price_id').val(response.sale_price);
                        $('#retail_price_id').val(response.retail_price);
                        $('#supplier_person_id').val(response.supplier_person);
                    }
                },
                error: function() {}
            });
        })

  
        $(document).on('change', '#choices-multiple1', function() {
            // $("#choices-multiple1").empty();

            let prod_name = $(this).val();
            $.ajax({
                url: 'getproductdeliveryperson',
                type: 'get',
                dataType: 'json',
                data: {
                    'serial_number': prod_name
                },
                success: function(response) {
                    
                    if (response != null) {
                        console.log("Dels",response);
                        $('#delivery_person_id').val(response.delivery_person);
                        $('#returning_person_id').val(response.deliveryman.first_name +' '+response.deliveryman.last_name);
                            // $('#myownselect option[value="' + response.deliveryman.first_name +' '+response.deliveryman.last_name + '"]').attr('selected', 'selected');
                        $("#myownselect").val(response.deliveryman.first_name +' '+response.deliveryman.last_name);
                        // $("#myownselect").val(response.deliveryman.id);
                        $("#myownselect2").val(response.deliveryman.id);
                        // $("#myownselect").select2();   

                  
                    }
                },
                error: function() {}
            });

        })


        $(document).on('change', '#choices-multiple1', function() {
            let prod_name = $(this).val();
            $.ajax({
                url: 'getproductreceivingperson',
                type: 'get',
                dataType: 'json',
                data: {
                    'serial_number': prod_name
                },
                success: function(response) {
                    if (response != null) {
                        $('#receiving_person_id').val(response.receiving_person);
                    }
                },
                error: function() {}
            });
        })

        $(document).on('change', '#myownselect', function(){

           let prod_name = $(this).val();
            $.ajax({
                url: 'getproductdeliveryperson2',
                type: 'get',
                dataType: 'json',
                data: {
                    'returning_person': prod_name
                },
                success: function(response) {
                    if (response != null) {

                        console.log("DATA",response);
                        
                        $("#myownselect2").val(response.id);
                        // $("#myownselect").select2();   

                  
                    }
                },
                error: function() {}
            });
        })
        

    });

</script>