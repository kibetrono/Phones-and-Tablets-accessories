<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                  <form action="">
        @csrf
        <div class="form-group">
            <label for="">model name</label><br>
            <select name="model_name" data-dependent='imei_number' class="form-control" id="model_name">
                <option value="">Select Name</option>
                @foreach ($alldata as $data)
                    <option value="{{$data->model_name}}">{{$data->model_name}}</option>
                @endforeach
            </select>
        </div>

         <div class="form-group">
            <label for="">imei number</label><br>
            <select name="imei_number" class="form-control" id="imei_number">
                <option value="">Select Number</option>
            </select>
        </div>
    </form>
            </div>
        </div>
    </div>
  
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    $(document).ready(function(){

        $(document).on('change','#model_name',function(){
                var val=$(this).val();
          
            if(val != ''){
                var select=$(this).attr('id');
                var value=$(this).val();
                var dependent=$(this).data('dependent')
                var _token=$('input[name="_token"]').val()
                // alert(dependent)

                $.ajax({

                    url:"{{route('dynamicdependent')}}",
                    method:'POST',
                    data:{
                        select:select,
                        value:value,
                        dependent:dependent,
                        _token:_token
                    },
                    success:function(response){
                        console.log("Data",response);

                        $('#'+dependent).html(response)
                    },
                    error:function(error){
                        console.log('Error',error);
                    }
                })
            }else{

            }
        })
    })
</script>
</body>
</html>