@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Manage Delivery Person Details')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('deliveryman.index')}}">{{__('Delivery Person')}}</a></li>
    <li class="breadcrumb-item">{{$deliveryperson['name']}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @can('create invoice')
            <a href="" class="btn btn-sm btn-primary">
                {{__('Create Invoice')}}
            </a>
        @endcan
        @can('create proposal')
                <a href="" class="btn btn-sm btn-primary">
                    {{__('Create Proposal')}}
                </a>

        @endcan
            <a href="" class="btn btn-sm btn-primary">
                {{__('Statement')}}
            </a>

             @can('edit customer')
                <a href="#" data-size="xl" data-url="{{ route('deliveryman.edit',$deliveryperson['id']) }}" data-ajax-popup="true" title="{{__('Edit Delivery Person')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-pencil"></i>
                </a>
             @endcan


        @can('delete customer')
                {!! Form::open(['method' => 'DELETE','class' => 'delete-form-btn', 'route' => ['deliveryman.destroy', $deliveryperson['id']]]) !!}

                <a href="#" data-bs-toggle="tooltip" title="{{__('Delete Delivery Person')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{ $deliveryperson['id']}}').submit();" class="btn btn-sm btn-danger bs-pass-para">
                    <i class="ti ti-trash text-white"></i>
                </a>
                {!! Form::close() !!}

        @endcan
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card customer-detail-box">
                <div class="card-body">
                    <h5 class="card-title">{{__('Delivery Person Info')}}</h5>
                    <p class="card-text mb-0"><strong>First Name: </strong> {{$deliveryperson['first_name']}}</p>
                    <p class="card-text mb-0"><strong>Last Name: </strong> {{$deliveryperson['last_name']}}</p>
                    <p class="card-text mb-0"><strong>ID Number: </strong> {{$deliveryperson['id_number']}}</p>
                    <p class="card-text mb-0"><strong>Email:</strong>  {{$deliveryperson['email']}}</p>
                    <p class="card-text mb-0"><strong>Contact:</strong>  {{$deliveryperson['contact']}}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-lg-8 col-xl-8">
            <div class="col-md-12">
                <div class="card pb-0">
                    <div class="card-body">
                        <h5 class="card-title">{{__('Company Info')}}</h5>
    
                        <div class="row">
                          
                            <div class="col-md-3 col-sm-6">
                                <div class="">
                                    <p class="card-text mb-2">{{__('Delivery Person Id')}}</p>
                                    <h6 class="report-text mb-3">{{AUth::user()->deliverymanNumberFormat($deliveryperson['deliveryman_id'])}}</h6>
                                   
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="">
                                    <p class="card-text mb-2">{{__('Date of Creation')}}</p>
                                    <h6 class="report-text mb-3">{{\Auth::user()->dateFormat($deliveryperson['created_at'])}}</h6>
                                  
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="">
                                    <p class="card-text mb-2">{{__('Balance')}}</p>
                                    <h6 class="report-text mb-3">{{\Auth::user()->priceFormat($deliveryperson['balance'])}}</h6>
                                    
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="">
                                    <p class="card-text mb-2">{{__('Overdue')}}</p>
                                    <h6 class="report-text mb-3">{{\Auth::user()->priceFormat($deliveryperson->customerOverdue($deliveryperson['id']))}}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

   
    <div class="row">
        <div class="col-12">
            <h5 class="h4 d-inline-block font-weight-400 mb-4">{{__('Delivered Items')}}</h5>
            <div class="card">
                <div class="card-body table-border-style table-border-style">

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr role="row">
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('IMEI No.') }}</th>
                                    <th>{{ __('Serial No.') }}</th>
                                    <th>{{ __('Sale Price') }}</th>
                                    <th>{{ __('Recommended Retail Price') }}</th>
                                    <th>{{ __('Invoice No.') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach ($delivered_items as $item)
                                    <tr>
                                        <td>{{$item->model_name}}</td>
                                        <td>{{$item->imei_number}}</td>
                                        <td>{{$item->serial_number}}</td>
                                        <td>{{$item->sale_price}}</td>
                                        <td>{{$item->retail_price}}</td>
                                        <td>{{$item->invoice_number}}</td>
                                    </tr>
                                @endforeach
                       
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
      <div class="row">
        <div class="col-12">
            <h5 class="h4 d-inline-block font-weight-400 mb-4">{{__('Returned Items')}}</h5>
            <div class="card">
                <div class="card-body table-border-style table-border-style">

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr role="row">
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('IMEI No.') }}</th>
                                    <th>{{ __('Serial No.') }}</th>
                                    <th>{{ __('Sale Price') }}</th>
                                    <th>{{ __('Recommended Retail Price') }}</th>
                                    <th>{{ __('Invoice No.') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($returned_items as $item)
                                    <tr>
                                        <td>{{$item->model_name}}</td>
                                        <td>{{$item->imei_number}}</td>
                                        <td>{{$item->serial_number}}</td>
                                        <td>{{$item->sale_price}}</td>
                                        <td>{{$item->retail_price}}</td>
                                        <td>{{$item->invoice_number}}</td>
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
