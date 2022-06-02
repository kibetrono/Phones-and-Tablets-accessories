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
        <a href="#" data-size="md" data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('productservice.file.import') }}" data-ajax-popup="true" data-title="{{__('Import product CSV file')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{route('productservice.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>

       <a href="#" data-size="lg" data-url="{{ route('productintake.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Product Intake')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>

    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="collapse multi-collapse mt-2 {{isset($_GET['category'])?'show':''}}" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['productservice.index'], 'method' => 'GET', 'id' => 'product_service']) }}
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                        
                            </div>
                            <div class="col-auto float-end ms-2">
                                <a href="#" class="btn btn-sm btn-primary"
                                   onclick="document.getElementById('product_service').submit(); return false;"
                                   data-bs-toggle="tooltip" title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('productservice.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
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
                                <th>{{ __('Sku') }}</th>
                                <th>{{ __('IMEI No.') }}</th>
                                <th>{{ __('Serial No.') }}</th>
                                <th>{{ __('Invoice No.') }}</th>
                                 <th>{{ __('Action') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($productServices as $productService)
                                <tr class="font-style">
                                    <td>{{ $productService->model_name }}</td>
                                    <td>{{ $productService->sku }}</td>
                                    <td>{{ $productService->imei_number }}</td>
                                    <td>{{ $productService->serial_number }}</td>
                                    <td>{{ $productService->invoice_number }}</td>
 
                                    {{-- <a data-size="md" href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('productstock.edit', $productService->id) }}" data-ajax-popup="true"  data-size="xl" data-bs-toggle="tooltip" title="{{__('Update Quantity')}}">
                                        <i class="ti ti-plus text-white"></i>
                                    </a> --}}

                                    <td class="Action">
                                  

                                    @can('edit customer')
                                        <div class="action-btn bg-primary ms-2">
                                            <a data-size="md" href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('productintake.edit', $productService->id) }}" data-ajax-popup="true"  data-size="xl" data-bs-toggle="tooltip" title="{{__('Update')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                     @endcan

                                    @can('delete customer')
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['productintake.destroy', $productService['id']],'id'=>'delete-form-'.$productService['id']]) !!}
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
