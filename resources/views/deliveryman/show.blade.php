@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Manage Deliveryman-Detail')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('deliverman.index')}}">{{__('Deliveryman')}}</a></li>
    <li class="breadcrumb-item">{{$deliverman['name']}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create invoice')
            <a href="{{ route('invoice.create',$deliverman->id) }}" class="btn btn-sm btn-primary">
                {{__('Create Invoice')}}
            </a>
        @endcan
        @can('create proposal')
                <a href="{{ route('proposal.create',$deliverman->id) }}" class="btn btn-sm btn-primary">
                    {{__('Create Proposal')}}
                </a>

        @endcan
            <a href="{{ route('deliverman.statement',$deliverman['id']) }}" class="btn btn-sm btn-primary">
                {{__('Statement')}}
            </a>

             @can('edit customer')
                <a href="#" data-size="xl" data-url="{{ route('deliverman.edit',$deliverman['id']) }}" data-ajax-popup="true" title="{{__('Edit Deliveryman')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-pencil"></i>
                </a>
             @endcan


        @can('delete customer')
                {!! Form::open(['method' => 'DELETE','class' => 'delete-form-btn', 'route' => ['deliverman.destroy', $deliverman['id']]]) !!}

                <a href="#" data-bs-toggle="tooltip" title="{{__('Delete Deliveryman')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{ $deliverman['id']}}').submit();" class="btn btn-sm btn-danger bs-pass-para">
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
                    <h5 class="card-title">{{__('Deliveryman Info')}}</h5>
                    <p class="card-text mb-0">{{$deliverman['name']}}</p>
                    <p class="card-text mb-0">{{$deliverman['email']}}</p>
                    <p class="card-text mb-0">{{$deliverman['contact']}}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card customer-detail-box">
                <div class="card-body">
                    <h5 class="card-title">{{__('Billing Info')}}</h5>
                    <p class="card-text mb-0">{{$deliverman['billing_name']}}</p>
                    <p class="card-text mb-0">{{$deliverman['billing_phone']}}</p>
                    <p class="card-text mb-0">{{$deliverman['billing_address']}}</p>
                    <p class="card-text mb-0">{{$deliverman['billing_zip']}}</p>
                    <p class="card-text mb-0">{{$deliverman['billing_city'].', '. $deliverman['billing_state'] .', '.$deliverman['billing_country']}}</p>
                </div>
            </div>

        </div>
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="card customer-detail-box">
                <div class="card-body">
                    <h5 class="card-title">{{__('Shipping Info')}}</h5>
                    <p class="card-text mb-0">{{$deliverman['shipping_name']}}</p>
                    <p class="card-text mb-0">{{$deliverman['shipping_phone']}}</p>
                    <p class="card-text mb-0">{{$deliverman['shipping_address']}}</p>
                    <p class="card-text mb-0">{{$deliverman['shipping_zip']}}</p>
                    <p class="card-text mb-0">{{$deliverman['shipping_city'].', '. $deliverman['billing_state'] .', '.$deliverman['billing_country']}}</p>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card pb-0">
                <div class="card-body">
                    <h5 class="card-title">{{__('Company Info')}}</h5>

                    <div class="row">
                        @php
                            $totalInvoiceSum=$deliverman->customerTotalInvoiceSum($deliverman['id']);
                            $totalInvoice=$deliverman->customerTotalInvoice($deliverman['id']);
                            $averageSale=($totalInvoiceSum!=0)?$totalInvoiceSum/$totalInvoice:0;
                        @endphp
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Customer Id')}}</p>
                                <h6 class="report-text mb-3">{{AUth::user()->customerNumberFormat($deliverman['customer_id'])}}</h6>
                                <p class="card-text mb-0">{{__('Total Sum of Invoices')}}</p>
                                <h6 class="report-text mb-0">{{\Auth::user()->priceFormat($totalInvoiceSum)}}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Date of Creation')}}</p>
                                <h6 class="report-text mb-3">{{\Auth::user()->dateFormat($deliverman['created_at'])}}</h6>
                                <p class="card-text mb-0">{{__('Quantity of Invoice')}}</p>
                                <h6 class="report-text mb-0">{{$totalInvoice}}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Balance')}}</p>
                                <h6 class="report-text mb-3">{{\Auth::user()->priceFormat($deliverman['balance'])}}</h6>
                                <p class="card-text mb-0">{{__('Average Sales')}}</p>
                                <h6 class="report-text mb-0">{{\Auth::user()->priceFormat($averageSale)}}</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-4">
                                <p class="card-text mb-0">{{__('Overdue')}}</p>
                                <h6 class="report-text mb-3">{{\Auth::user()->priceFormat($deliverman->customerOverdue($deliverman['id']))}}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5 class="h4 d-inline-block font-weight-400 mb-4">{{__('Proposal')}}</h5>
            <div class="card">
                <div class="card-body table-border-style table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{__('Proposal')}}</th>
                                <th>{{__('Issue Date')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($deliverman->customerProposal($deliverman->id) as $proposal)
                                <tr>
                                    <td class="Id">
                                        @if(\Auth::guard('customer')->check())
                                            <a href="{{ route('customer.proposal.show',\Crypt::encrypt($proposal->id)) }}" class="btn btn-outline-primary">{{ AUth::user()->proposalNumberFormat($proposal->proposal_id) }}
                                            </a>
                                        @else
                                            <a href="{{ route('proposal.show',\Crypt::encrypt($proposal->id)) }}" class="btn btn-outline-primary">{{ AUth::user()->proposalNumberFormat($proposal->proposal_id) }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ Auth::user()->dateFormat($proposal->issue_date) }}</td>
                                    <td>{{ Auth::user()->priceFormat($proposal->getTotal()) }}</td>
                                    <td>
                                        @if($proposal->status == 0)
                                            <span class="badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @elseif($proposal->status == 1)
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @elseif($proposal->status == 2)
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @elseif($proposal->status == 3)
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @elseif($proposal->status == 4)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Proposal::$statues[$proposal->status]) }}</span>
                                        @endif
                                    </td>
                                    @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                        <td class="Action">
                                            <span>
                                            @can('convert invoice')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" data-original-title="{{__('Convert to Invoice')}}" title="{{__('Convert to Invoice')}}" data-confirm="You want to confirm convert to invoice. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('proposal-form-{{$proposal->id}}').submit();">
                                                        <i class="ti ti-exchange text-white"></i>
                                                        {!! Form::open(['method' => 'get', 'route' => ['proposal.convert', $proposal->id],'id'=>'proposal-form-'.$proposal->id]) !!}
                                                            {!! Form::close() !!}
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('duplicate proposal')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-original-title="{{__('Duplicate')}}"  title="{{__('Duplicate Proposal')}}" data-confirm="You want to confirm duplicate this invoice. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-{{$proposal->id}}').submit();">
                                                        <i class="ti ti-copy text-white text-white"></i>
                                                        {!! Form::open(['method' => 'get', 'route' => ['proposal.duplicate', $proposal->id],'id'=>'duplicate-form-'.$proposal->id]) !!}
                                                            {!! Form::close() !!}
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('show proposal')
                                                    @if(\Auth::guard('customer')->check())
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('customer.proposal.show',$proposal->id) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"  title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                            <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('proposal.show',\Crypt::encrypt($proposal->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                                <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('edit proposal')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route('proposal.edit',\Crypt::encrypt($proposal->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('delete proposal')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['proposal.destroy', $proposal->id],'id'=>'delete-form-'.$proposal->id]) !!}

                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip"  title="Delete" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$proposal->id}}').submit();">
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
    <div class="row">
        <div class="col-12">
            <h5 class="h4 d-inline-block font-weight-400 mb-4">{{__('Invoice')}}</h5>
            <div class="card">
                <div class="card-body table-border-style table-border-style">

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{__('Invoice')}}</th>
                                <th>{{__('Issue Date')}}</th>
                                <th>{{__('Due Date')}}</th>
                                <th>{{__('Due Amount')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($deliverman->customerInvoice($deliverman->id) as $invoice)
                                <tr>
                                    <td class="Id">
                                        @if(\Auth::guard('customer')->check())
                                            <a href="{{ route('customer.invoice.show',\Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}
                                            </a>
                                        @else
                                            <a href="{{ route('invoice.show',\Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ \Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                    <td>
                                        @if(($invoice->due_date < date('Y-m-d')))
                                            <p class="text-danger"> {{ \Auth::user()->dateFormat($invoice->due_date) }}</p>
                                        @else
                                            {{ \Auth::user()->dateFormat($invoice->due_date) }}
                                        @endif
                                    </td>
                                    <td>{{\Auth::user()->priceFormat($invoice->getDue())  }}</td>
                                    <td>
                                        @if($invoice->status == 0)
                                            <span class="badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 1)
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 2)
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 3)
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @elseif($invoice->status == 4)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                        @endif
                                    </td>
                                    @if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <td class="Action">
                                            <span>
                                            @can('duplicate invoice')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-original-title="{{__('Duplicate')}}" title="{{__('Duplicate Invoice')}}" data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-{{$invoice->id}}').submit();">
                                                        <i class="ti ti-copy text-white text-white"></i>
                                                        {!! Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id],'id'=>'duplicate-form-'.$invoice->id]) !!}
                                                            {!! Form::close() !!}
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('show invoice')
                                                    @if(\Auth::guard('customer')->check())
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('customer.invoice.show',\Crypt::encrypt($invoice->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                                <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="{{ route('invoice.show',\Crypt::encrypt($invoice->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                                <i class="ti ti-eye text-white text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('edit invoice')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route('invoice.edit',\Crypt::encrypt($invoice->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete invoice')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id],'id'=>'delete-form-'.$invoice->id]) !!}

                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$invoice->id}}').submit();">
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
