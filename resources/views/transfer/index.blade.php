@extends('layouts.admin')
@section('page-title')
    {{__('Bank Balance Transfer')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Bank Balance Transfer')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">
            <i class="ti ti-filter"></i>
        </a>
        @can('create transfer')
            <a href="#" data-url="{{ route('transfer.create') }}" data-ajax-popup="true" data-title="{{__('Create Transfer')}}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="collapse multi-collapse mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('transfer.index'),'method' => 'GET','id'=>'transfer_form')) }}
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::text('date', isset($_GET['date'])?$_GET['date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1')) }}


                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::select('f_account',$account,isset($_GET['f_account'])?$_GET['f_account']:'', array('class' => 'form-control select2')) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::select('t_account', $account,isset($_GET['t_account'])?$_GET['t_account']:'', array('class' => 'form-control select2')) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2">

                                <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('transfer_form').submit(); return false;" data-bs-toggle="tooltip"  title="Apply" data-original-title="{{__('apply')}}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>

                                <a href="{{ route('productservice.index') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                   title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash text-white-off "></i></span>
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Date')}}</th>
                                <th> {{__('From Account')}}</th>
                                <th> {{__('To Account')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Reference')}}</th>
                                <th> {{__('Description')}}</th>
                                @if(Gate::check('edit transfer') || Gate::check('delete transfer'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($transfers as $transfer)
                                <tr class="font-style">
                                    <td>{{ \Auth::user()->dateFormat( $transfer->date) }}</td>
                                    <td>{{ !empty($transfer->fromBankAccount())? $transfer->fromBankAccount()->bank_name.' '.$transfer->fromBankAccount()->holder_name:''}}</td>
                                    <td>{{!empty( $transfer->toBankAccount())? $transfer->toBankAccount()->bank_name.' '. $transfer->toBankAccount()->holder_name:''}}</td>
                                    <td>{{  \Auth::user()->priceFormat( $transfer->amount)}}</td>
                                    <td>{{  $transfer->reference}}</td>
                                    <td>{{  $transfer->description}}</td>
                                    @if(Gate::check('edit transfer') || Gate::check('delete transfer'))
                                        <td class="Action">
                                            <span>
                                            @can('edit transfer')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('transfer.edit',$transfer->id) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-title="{{__('Edit Transfer')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete transfer')
                                                    <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['transfer.destroy', $transfer->id],'id'=>'delete-form-'.$transfer->id]) !!}

                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$transfer->id}}').submit();">
                                                            <i class="ti ti-trash text-white text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    @endif
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

