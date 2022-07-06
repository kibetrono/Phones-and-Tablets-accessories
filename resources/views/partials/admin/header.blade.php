@php
    $users=\Auth::user();
    $profile=asset(Storage::url('uploads/avatar/'));
    $currantLang = $users->currentLanguage();
    $languages=App\Models\Utility::languages();
    $mode_setting = \App\Models\Utility::mode_layout();

@endphp
<header class="dash-header  {{(isset($mode_setting['cust_theme_bg']) && $mode_setting['cust_theme_bg'] == 'on')?'transprent-bg':''}}">
    <div class="header-wrapper">
    <div class="me-auto dash-mob-drp">
    <ul class="list-unstyled">
        <li class="dash-h-item mob-hamburger">
            <a href="#!" class="dash-head-link" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner">
                        </div>
                    </div>
                </div>
             </a>
        </li>

        <li class="dropdown dash-h-item drp-company">
            <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                            href="#"
                            role="button"
                            aria-haspopup="false"
                            aria-expanded="false"
            >
            <span class="theme-avtar">
                <img src="{{(!empty(\Auth::user()->avatar))? asset(Storage::url("uploads/avatar/".\Auth::user()->avatar)): asset(Storage::url("uploads/avatar/avatar.png"))}}" class="img-fluid rounded-circle">
            </span>
                <span class="hide-mob ms-2">{{\Auth::user()->name}}</span>
                    <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                </a>
                <div class="dropdown-menu dash-h-dropdown">

                    @if(\Auth::guard('customer')->check())
                        <a href="{{route('customer.profile')}}" class="dropdown-item">
                            <i class="ti ti-user"></i> <span>{{__('My Profile')}}</span>
                        </a>
                    @elseif(\Auth::guard('vender')->check())
                        <a href="{{route('supplier.profile')}}" class="dropdown-item">
                            <i class="ti ti-user"></i> <span>{{__('My Profile')}}</span>
                        </a>
                    @else
                        <a href="{{route('profile')}}" class="dropdown-item">
                            <i class="ti ti-user"></i> <span>{{__('My Profile')}}</span>
                        </a>
                    @endif

                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();" class="dropdown-item">
                        <i class="ti ti-power"></i>
                            <span>{{__('Logout')}}</span>
                    </a>
                    <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                            {{ csrf_field() }}
                    </form>

                </div>
        </li>

        @if( Gate::check('create product & service') ||  Gate::check('create customer') ||  Gate::check('create vender')||  Gate::check('create proposal')||  Gate::check('create invoice')||  Gate::check('create bill') ||  Gate::check('create goal') ||  Gate::check('create bank account'))
            <li class="dropdown dash-h-item ml-2">
                    <div class="dropdown notification-icon">
                        <a class="dash-head-link dropdown-toggle arrow-none ms-0" data-bs-toggle="dropdown"
                             href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-bookmark "></i>
                            </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownBookmark">
                                @if(Gate::check('create product & service'))
                                    <a class="dropdown-item" href="#" data-url="{{ route('productservice.create') }}" data-ajax-popup="true" data-title="{{__('Create New Product')}}"><i class="ti ti-shopping-cart"></i>{{__('Create New Product')}}</a>
                                @endif
                                @if(Gate::check('create customer'))
                                    <a class="dropdown-item" href="#" data-url="{{ route('customer.create') }}" data-ajax-popup="true" data-title="{{__('Create New Customer')}}"><i class="ti ti-user"></i>{{__('Create New Customer')}}</a>
                                @endif
                                @if(Gate::check('create vender'))
                                    <a class="dropdown-item" href="#" data-url="{{ route('supplier.create') }}" data-ajax-popup="true" data-title="{{__('Create New Vendor')}}"><i class="ti ti-note"></i>{{__('Create New Supplier')}}</a>
                                @endif
                                @if(Gate::check('create customer'))
                                <a class="dropdown-item" href="#" data-url="{{ route('deliveryman.create') }}" data-ajax-popup="true" data-title="{{__('Create New Delivery Person')}}"><i class="ti ti-note"></i>{{__('Create New Delivery Person')}}</a>
                            @endif
                                @if(Gate::check('create proposal'))
                                    <a class="dropdown-item" href="{{ route('proposal.create',0) }}"><i class="ti ti-file"></i>{{__('Create New Proposal')}}</a>
                                @endif
                                @if(Gate::check('create invoice'))
                                    <a class="dropdown-item" href="{{ route('invoice.create',0) }}"><i class="ti ti-file-invoice"></i>{{__('Create New Invoice')}}</a>
                                @endif
                                @if(Gate::check('create bill'))
                                    <a class="dropdown-item" href="{{ route('bill.create',0) }}"><i class="ti ti-report-money"></i>{{__('Create New Bill')}}</a>
                                @endif
                                @if(Gate::check('create bank account'))
                                    <a class="dropdown-item" href="#" data-url="{{ route('bank-account.create') }}" data-ajax-popup="true" data-title="{{__('Create New Account')}}"><i class="ti ti-building-bank"></i>{{__('Create New Account')}}</a>
                                @endif
                                @if(Gate::check('create goal'))
                                    <a class="dropdown-item " href="#" data-url="{{ route('goal.create') }}" data-ajax-popup="true" data-title="{{__('Create New Goal')}}"><i class="ti ti-target "></i>{{__('Create New Goal')}}</a>
                                @endif
                            </div>
                    </div>
                </li>
        @endif
    </ul>
</div>
            {{-- <div class="ms-auto">
              <ul class="list-unstyled">
                <li class="dropdown dash-h-item drp-language">
                    <a
                        class="dash-head-link dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        aria-expanded="false"
                    >
                        <i class="ti ti-world nocolor"></i>
                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">

                                @if(\Auth::user()->type=='super admin')
                                    <a class="dropdown-item " href="{{route('manage.language',[$currantLang])}}">{{ __('Create & Customize') }}</a>
                                @endif
                                @foreach($languages as $language)
                                    <a class="dropdown-item @if($language == $currantLang) text-danger @endif" href="{{route('change.language',$language)}}">{{Str::upper($language)}}</a>
                                @endforeach
                    </div>
                </li>
              </ul>
            </div> --}}

</div>
</header>
