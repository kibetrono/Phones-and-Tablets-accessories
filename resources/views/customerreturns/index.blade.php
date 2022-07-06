@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar/'));
@endphp
@push('script-page')
    <script>
        </script>

@endpush
@section('page-title')
    {{__('Manage Customer Returns')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Customer Returns')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="md"  data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('customerreturns.file.import') }}" data-ajax-popup="true" data-title="{{__('Import Customer Returns CSV file')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{route('customerreturns.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>

       <a href="#" data-size="lg" data-url="{{ route('customerreturns.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Customer Returns')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>

    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    
                                    <th> {{__('Product Name')}}</th>
                                    <th> {{__('IMEI NO.')}}</th>
                                    <th> {{__('SERIAL NO.')}}</th>
                                    <th> {{__('CUSTOMERS NAME')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                           
                                @foreach ($customerReturns as $k=>$cust_returns)
                                <tr class="custreturns_tr" id="custreturns_detail" data-url="{{route('customerreturns.show',$cust_returns['id'])}}" data-id="{{$cust_returns['id']}}">
                                    
                                    <td class="font-style">{{$cust_returns['model_name']}}</td>
                                    <td>{{$cust_returns['imei_number']}}</td>
                                    <td>{{$cust_returns['serial_number']}}</td>

                                    <td>{{$cust_returns['returning_customer']}}</td>
                                   
                                    <td class="Action">

                                    @can('delete product & service')
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['customerreturns.destroy', $cust_returns['id']],'id'=>'delete-form-'.$cust_returns['id']]) !!}
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
