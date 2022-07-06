@php
$logo=asset(Storage::url('uploads/logo/'));
$company_favicon=Utility::getValByName('company_favicon');
$SITE_RTL = env('SITE_RTL');

$setting = \App\Models\Utility::colorset();
$color = 'theme-3';
if (!empty($setting['color'])) {
$color = $setting['color'];
}

$mode_setting = \App\Models\Utility::mode_layout();
@endphp

<!DOCTYPE html>
<html lang="en" dir="{{$SITE_RTL == 'on'?'rtl':''}}">

<head>
    <title>{{(Utility::getValByName('title_text')) ? Utility::getValByName('title_text') : config('app.name', 'ERPGO')}} - {{__('Manage The Product Intake')}}</title>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

    <!-- Meta -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="url" content="{{ url('').'/'.config('chatify.path') }}" data-user="{{ Auth::user()->id }}">

    <!-- Favicon icon -->
    <link rel="icon" href="{{$logo.'/'.(isset($company_favicon) && !empty($company_favicon)?$company_favicon:'favicon.png')}}" type="image" sizes="16x16">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">

    <link rel="stylesheet" href="{{asset('assets/css/plugins/datepicker-bs5.min.css')}}">

    <!--bootstrap switch-->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">


    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
    <!-- vendor css -->

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">

    @if (env('SITE_RTL') == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
    @else
    @if( isset($mode_setting['cust_darklayout']) && $mode_setting['cust_darklayout'] == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">

    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" id="main-style-link">
    @stack('css-page')
</head>

<body class="{{ $color }}">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    @include('partials.admin.menu')
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->
    @include('partials.admin.header')

    <!-- Modal -->
    <div class="modal notification-modal fade" id="notification-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                    <h6 class="mt-2">
                        <i data-feather="monitor" class="me-2"></i>Desktop settings
                    </h6>
                    <hr />
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting1" checked />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting1">Allow desktop notification</label>
                    </div>
                    <p class="text-muted ms-5">
                        you get lettest content at a time when data will updated
                    </p>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting2" />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting2">Store Cookie</label>
                    </div>
                    <h6 class="mb-0 mt-5">
                        <i data-feather="save" class="me-2"></i>Application settings
                    </h6>
                    <hr />
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting3" />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting3">Backup Storage</label>
                    </div>
                    <p class="text-muted mb-4 ms-5">
                        Automaticaly take backup as par schedule
                    </p>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting4" />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting4">Allow guest to print file</label>
                    </div>
                    <h6 class="mb-0 mt-5">
                        <i data-feather="cpu" class="me-2"></i>System settings
                    </h6>
                    <hr />
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="pcsetting5" checked />
                        <label class="form-check-label f-w-600 pl-1" for="pcsetting5">View other user chat</label>
                    </div>
                    <p class="text-muted ms-5">Allow to show public user message</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-danger btn-sm" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-light-primary btn-sm">
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Header ] end -->


    <!-- [ Main Content ] start -->
    <div class="dash-container">
        <div class="dash-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="page-header-title">
                                <h4 class="m-b-10">{{__('Manage The Product Intake')}}</h4>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
                                <li class="breadcrumb-item">{{__('Product intake')}}</li>
                            </ul>
                        </div>
                        <div class="col">
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
                        </div>
                    </div>
                </div>
            </div>
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
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('product_service').submit(); return false;" data-bs-toggle="tooltip" title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('productintake.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
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

                                    <tbody id="gshshs">
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
                                                    <a data-size="md" href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('productintake.edit', $productIntake->id) }}" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="{{__('Update')}}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                @endcan

                                                @can('delete customer')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['productintake.destroy', $productIntake['id']],'id'=>'delete-form-'.$productIntake['id']]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
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
            </div> <!-- [ Main Content ] end -->
        </div>
    </div>
    <div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>


    <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
        <div id="liveToast" class="toast text-white  fade" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
    <footer class="dash-footer">
        <div class="footer-wrapper">
            <div class="py-1">
                <span class="text-muted"> {{(Utility::getValByName('footer_text')) ? Utility::getValByName('footer_text') :  __('Copyright AccountGo SaaS') }} {{ date('Y') }}</span>
            </div>
            <div class="py-1">
                {{-- <ul class="list-inline m-0">--}}
                {{-- <li class="list-inline-item">--}}
                {{-- <a class="link-secondary" href="javascript:">Joseph William</a>--}}
                {{-- </li>--}}
                {{-- <li class="list-inline-item">--}}
                {{-- <a class="link-secondary" href="javascript:">About Us</a>--}}
                {{-- </li>--}}
                {{-- <li class="list-inline-item">--}}
                {{-- <a class="link-secondary" href="javascript:">Blog</a>--}}
                {{-- </li>--}}
                {{-- <li class="list-inline-item">--}}
                {{-- <a class="link-secondary" href="javascript:">Library</a>--}}
                {{-- </li>--}}
                {{-- </ul>--}}
            </div>
        </div>
    </footer>



    <!-- Warning Section Ends -->
    <!-- Required Js -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/dash.js') }}"></script>

    <script src="{{asset('assets/js/plugins/datepicker-full.min.js')}}"></script>

    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>

    <script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/productintake-simple-datatables.js') }}"></script>

    <!-- sweet alert Js -->
    {{--<script src="{{ asset('assets/js/plugins/sweetalert.min.js') }}"></script>--}}


    <!--Botstrap switch-->
    <script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>


    <!-- Apex Chart -->
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>

    <script src="{{ asset('js/custom.js') }}"></script>

    @if($message = Session::get('success'))
    <script>
        show_toastr('success', '{!! $message !!}');
    </script>
    @endif
    @if($message = Session::get('error'))
    <script>
        show_toastr('error', '{!! $message !!}');
    </script>
    @endif
    @stack('script-page')




    <script>
        feather.replace();

        function removeClassByPrefix(node, prefix) {
            for (let i = 0; i < node.classList.length; i++) {
                let value = node.classList[i];
                if (value.startsWith(prefix)) {
                    node.classList.remove(value);
                }
            }
        }
    </script>
</body>

</html>