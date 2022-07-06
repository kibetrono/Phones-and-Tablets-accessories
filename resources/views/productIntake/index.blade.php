@extends('layouts.admin')
@section('page-title')
    {{__('Manage The Product Intake')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Product intake')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">
            <i class="ti ti-filter"></i>
        </a>
        <a href="#" data-size="md" data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('productintake.file.import') }}" data-ajax-popup="true" data-title="{{__('Import product CSV file')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{route('productintake.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>

       <a href="#" data-size="lg" data-url="{{ route('productintake.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Product Intake')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>

        <a href="#" data-size="lg" data-url="{{ route('productreturn.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Product Return')}}" data-title="{{__('Returned Product')}}" class="btn btn-sm btn-primary">
            <i class="fa fa-reply"></i>
        </a>

    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="collapse multi-collapse mt-2 {{isset($_GET['category'])?'show':''}}" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['productintake.index'], 'method' => 'GET', 'id' => 'product_service']) }}
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                        
                            </div>
                            <div class="col-auto float-end ms-2">
                                <a href="#" class="btn btn-sm btn-primary"
                                   onclick="document.getElementById('product_service').submit(); return false;"
                                   data-bs-toggle="tooltip" title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('productintake.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                   title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                </a>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr role="row">
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('IMEI No.') }}</th>
                                <th>{{ __('Serial No.') }}</th>
                                <th>{{ __('Sale Price') }}</th>
                                <th>{{ __('Recommended Retail Price') }}</th>
                                <th>{{ __('Supplier') }}</th>
                                {{-- <th style="color:yellow">{{ __('First Name') }}</th>
                                <th style="color:red">{{ __('First Name') }}</th>
                                 <th>{{ __('Status') }}</th> --}}
                                 <th>{{ __('Action') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($productIntakes as $productIntake)
                                <tr class="font-style">
                                    <td>{{ $productIntake->model_name }}</td>
                                    <td>{{ $productIntake->imei_number }}</td>
                                    <td>{{ $productIntake->serial_number }}</td>
                                    <td>{{ $productIntake->sale_price }}</td>
                                    <td>{{ $productIntake->retail_price }}</td>
                                    <td>{{ $productIntake->supplier_person }}</td>
                                    {{-- <td style="color:yellow">{{ $productIntake->productservice->name}}</td>
                                    <td style="color:red">{{ $productIntake->deliveryman->first_name }} {{$productIntake->deliveryman->last_name}}</td>
                                    <td style="color:blue">{{ $productIntake->status }} </td> --}}
                                    
                                    {{-- specName --}}
                                    {{-- <td>
                                        @if($productIntake->status == 0)
                                            <span class="badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\productIntake::$the_status[$productIntake->status]) }}</span>
                                        @elseif($productIntake->status == 1)
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\productIntake::$the_status[$productIntake->status]) }}</span>
                                        @elseif($productIntake->status == 2)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\productIntake::$the_status[$productIntake->status]) }}</span>
                                        @elseif($productIntake->status == 3)
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\productIntake::$the_status[$productIntake->status]) }}</span>
                                        @elseif($productIntake->status == 4)
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\productIntake::$the_status[$productIntake->status]) }}</span>
                                        @endif
                                    </td> --}}
                                    <td class="Action">
                                  
                                    @can('edit customer')
                                        <div class="action-btn bg-primary ms-2">
                                            <a data-size="md" href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('productintake.edit', $productIntake->id) }}" data-ajax-popup="true"  data-size="xl" data-bs-toggle="tooltip" title="{{__('Update')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                     @endcan

                                    @can('delete customer')
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['productintake.destroy', $productIntake['id']],'id'=>'delete-form-'.$productIntake['id']]) !!}
                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" ><i class="ti ti-trash text-white text-white"></i></a>
                                        {!! Form::close() !!}
                                    </div>
                                @endcan

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


