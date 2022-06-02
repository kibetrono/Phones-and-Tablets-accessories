@extends('layouts.auth')
@php
    $logo=asset(Storage::url('uploads/logo/'));
    $company_logo=Utility::getValByName('company_logo');
    $settings = Utility::settings();

@endphp
@push('custom-scripts')
    @if(env('RECAPTCHA_MODULE') == 'yes')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
@section('page-title')
    {{__('Login')}}
@endsection

@section('auth-lang')

    <select name="language" id="language" class="btn btn-primary my-1 me-2" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
        @foreach(App\Models\Utility::languages() as $language)
            <option @if($lang == $language) selected @endif value="{{ route('customer.login.lang',$language) }}">{{Str::upper($language)}}</option>
        @endforeach
    </select>
@endsection

@section('content')
    <div class="">
        <h2 class="mb-3 f-w-600">{{__('Sign in')}}</h2>
    </div>
    {{Form::open(array('route'=>'customer.login','method'=>'post','id'=>'loginForm'))}}
    @csrf
    <div class="">
        <div class="form-group mb-3">
            <label for="email" class="form-label">{{__('Enter Email address')}}</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
            <div class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label for="password" class="form-label">{{__('Enter Password')}}</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
            <div class="invalid-feedback" role="alert">{{ $message }}</div>
            @enderror

        </div>

        @if(env('RECAPTCHA_MODULE') == 'yes')
            <div class="form-group mb-3">
                {!! NoCaptcha::display() !!}
                @error('g-recaptcha-response')
                <span class="small text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </div>
        @endif
        <div class="form-group mb-4">
            <a href="{{ route('customer.change.langPass') }}" class="text-xs">{{ __('Forgot Your Password?') }}</a>

        </div>
        <div class="d-grid">
            <button type="submit" class="btn-login btn btn-primary btn-block mt-2" id="login_button">{{__('Sign In')}}</button>

        </div>
{{--        <p class="my-4 text-center">{{__("Don't have an account?")}} <a href="{{ route('register') }}" class="text-primary">{{__('Register')}}</a></p>--}}

        <div class="row">
            <div class="col-sm-6">
                <a href="{{route('login')}}" class="btn-login btn btn-primary btn-block mt-2 text-white">{{__('User Login')}}</a>

            </div>
            <div class="col-sm-6 text-end">
                <a href="{{route('vender.login')}}" class="btn-login btn btn-primary btn-block mt-2 text-white">{{__('Vender Login')}}</a>
            </div>
        </div>

    </div>
    {{Form::close()}}
@endsection

<script src="{{asset('js/jquery.min.js')}}"></script>
<script>
    $(document).ready(function () {
        $("#form_data").submit(function (e) {
            $("#login_button").attr("disabled", true);
            return true;
        });
    });
</script>





