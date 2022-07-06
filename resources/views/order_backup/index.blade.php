@extends('layouts.admin')
@section('page-title')
    {{__('Order')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Order')}}</li>
@endsection
{{-- start of add orders --}}
@section('action-btn')
    <div class="float-end">
       
       <a href="#" data-size="lg" data-url="{{ route('order.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create Order')}}" data-title="{{__('Create Order')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>

    </div>
@endsection
{{-- end of orders --}}
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
                                <th>{{__('Product Name')}}</th>
                                <th>{{__('Price')}}</th>
                                <th>{{__('Payment Type')}}</th>
                               
                                <th >{{__('Invoice')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{$order->order_id}}</td>
                                    <td>{{$order->created_at->format('d M Y')}}</td>
                                    <td>{{$order->model_name}}</td>
                                    {{-- <td>{{env('CURRENCY_SYMBOL').$order->price}}</td> --}}
                                    <td>Ksh.{{$order->price}}</td>
                                    <td>{{$order->payment_type}}</td>
                                    <td>{{$order->invoice_number}}</td>
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
