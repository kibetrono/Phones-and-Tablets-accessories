@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar/'));
@endphp
@push('script-page')
    <script>
        </script>

@endpush
@section('page-title')
    {{__('Manage Delivery Personel')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('DeliveryMan')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="md"  data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('deliveryman.file.import') }}" data-ajax-popup="true" data-title="{{__('Import customer CSV file')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{route('deliveryman.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>

       <a href="#" data-size="lg" data-url="{{ route('deliveryman.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Delivery Personel')}}" class="btn btn-sm btn-primary">
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
                                    <th>#</th>
                                    <th> {{__('Name')}}</th>
                                    <th> {{__('Contact')}}</th>
                                    <th> {{__('Email')}}</th>
                                    <th> {{__('Balance')}}</th>
                                    <th> {{__('Last Login')}}</th>
                                    <th>{{__('Action')}}</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                           
                                @foreach ($deliverypersons as $k=>$del_man)
                                <tr class="cust_tr" id="cust_detail" data-url="{{route('deliveryman.show',$del_man['id'])}}" data-id="{{$del_man['id']}}">
                                    <td class="Id">
                                        @can('show customer')
                                            <a href="{{ route('deliveryman.show',\Crypt::encrypt($del_man['id'])) }}" class="btn btn-outline-primary">
                                                {{ AUth::user()->deliverymanNumberFormat($del_man['deliveryman_id']) }}
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-outline-primary">
                                                {{ AUth::user()->deliverymanNumberFormat($del_man['deliveryman_id']) }}
                                            </a>
                                        @endcan 
                                    </td>
                                    <td class="font-style">{{$del_man['name']}}</td>
                                    <td>{{$del_man['contact']}}</td>
                                    <td>{{$del_man['email']}}</td>
                                    <td>{{\Auth::user()->priceFormat($del_man['balance'])}}</td>
                                    <td>
                                        {{ (!empty($del_man->last_login_at)) ? $del_man->last_login_at : '-' }}
                                    </td>
                                    <td class="Action">
                                        <span>
                                        @if($del_man['is_active']==0)
                                                <i class="ti ti-lock" title="Inactive"></i>
                                            @else

                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center" data-url="{{route('deliveryman.reset',\Crypt::encrypt($del_man['id']))}}" data-ajax-popup="true"  data-size="md" data-bs-toggle="tooltip" title="{{__('Forgot Password')}}"  data-title="{{__('Reset Password')}}">
                                                        <i class="ti ti-key text-white"></i>
                                                    </a>
                                                </div>
                                                @can('show customer')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('deliveryman.show',\Crypt::encrypt($del_man['id'])) }}" class="mx-3 btn btn-sm align-items-center"
                                                       data-bs-toggle="tooltip" title="{{__('View')}}">
                                                        <i class="ti ti-eye text-white text-white"></i>
                                                    </a>
                                                </div>
                                                @endcan
                                                @can('edit customer')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center" data-url="{{ route('deliveryman.edit',$del_man['id']) }}" data-ajax-popup="true"  data-size="xl" data-bs-toggle="tooltip" title="{{__('Edit')}}"  data-title="{{__('Edit Delivery Personel\'s Details')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>

                                                @endcan



                                                @can('delete customer')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['deliveryman.destroy', $del_man['id']],'id'=>'delete-form-'.$del_man['id']]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" ><i class="ti ti-trash text-white text-white"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan

                                            @endif
                                        </span>
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
