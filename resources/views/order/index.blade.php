@extends('layouts.admin')
@section('page-title')
    {{__('Order')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Order')}}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Order Id')}}</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Plan Name')}}</th>
                                <th>{{__('Price')}}</th>
                                <th>{{__('Payment Type')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Coupon')}}</th>
                                <th class="text-center">{{__('Invoice')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{$order->order_id}}</td>
                                    <td>{{$order->created_at->format('d M Y')}}</td>
                                    <td>{{$order->user_name}}</td>
                                    <td>{{$order->plan_name}}</td>
                                    <td>{{env('CURRENCY_SYMBOL').$order->price}}</td>
                                    <td>{{$order->payment_type}}</td>
                                    <td>
                                        @if($order->payment_status == 'succeeded')
                                            <i class="badge bg-success p-2 px-3 rounded"></i> {{ucfirst($order->payment_status)}}
                                        @else
                                            <i class="badge bg-danger p-2 px-3 rounded"></i> {{ucfirst($order->payment_status)}}
                                        @endif
                                    </td>

                                    <td>{{!empty($order->total_coupon_used)? !empty($order->total_coupon_used->coupon_detail)?$order->total_coupon_used->coupon_detail->code:'-':'-'}}</td>

                                    <td class="text-center">
                                        @if($order->receipt != 'free coupon' && $order->payment_type == 'STRIPE')
                                            <a href="{{$order->receipt}}" title="Invoice" target="_blank" class="">
                                                <i class="ti ti-file-invoice"></i>
                                            </a>
                                        @elseif($order->receipt == 'free coupon')
                                            <p>{{__('Used 100 % discount coupon code.')}}</p>
                                        @elseif($order->payment_type == 'Manually')
                                            <p>{{__('Manually plan upgraded by super admin')}}</p>
                                        @else
                                            -
                                        @endif
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
