{{-- <!DOCTYPE html>
<html>
<head>
    <title>Hi</title>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $date }}</p>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
</body>
</html> --}}

<html>
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <table class="table table-bordered">
    <thead>
      <tr>
        <th>
          {{__('Date')}}</th>
          <th>{{__('Product Name')}}</th>
          <th>{{__('Sale Price')}}</th>
          <th>{{__('Supplier Name')}}</th>
          <th>{{__('Status')}}</th>     
      </tr>
      </thead>
      <tbody>
        {{-- @foreach ($title as $item) --}}
            
      <tr>
        <td>
          {{{ $allproducts['title']['data'] }}}

          {{-- {{$item->updated_at}} --}}
          {{-- {{$allproducts}} --}}
        </td>

        <td>
{{-- {{$allproducts['title']['data']}} --}}

        </td>
{{-- {{$item->sale_price}} --}}

        <td>
{{-- {{$item_supplier_person}} --}}

        </td>
                <td>
{{-- {{$item->status}} --}}

        </td>
      </tr>
        {{-- @endforeach --}}

      </tbody>
    </table>
  </body>
</html>