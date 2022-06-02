@extends('layouts.auth')
@php
    $logo=asset(Storage::url('uploads/logo/'));
     $company_logo=App\Models\Utility::getValByName('company_logo');
@endphp

@section('page-title')
    {{__('Register')}}
@endsection
@push('custom-scripts')
    @if(env('RECAPTCHA_MODULE') == 'yes')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush


@section('auth-lang')
    <select class="btn btn-primary my-1 me-2 " onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
        @foreach(Utility::languages() as $language)
            <option class="" @if($lang == $language) selected @endif value="{{ route('login',$language) }}">{{Str::upper($language)}}</option>
        @endforeach
    </select>
@endsection

@section('content')
    <div class="">
        <h2 class="mb-3 f-w-600">{{__('Register')}}</h2>
    </div>
    <form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="">
        <div class="form-group">
            <label for="name" class="form-label">{{__('Full Name')}}</label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Enter Your Name" required autocomplete="name" autofocus>
            @error('name')
            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="email" class="form-label">{{__('Email')}}</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Enter Your Email"  required autocomplete="email" autofocus>
            @error('email')
            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
            @enderror
            <div class="invalid-feedback">
                {{__('Please fill in your email')}}
            </div>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">{{__('Password')}}</label>
            <input id="password" type="password" data-indicator="pwindicator" class="form-control pwstrength @error('password') is-invalid @enderror" name="password"  placeholder="Enter Your Password"  required autocomplete="new-password">
            @error('password')
            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
            @enderror
            <div id="pwindicator" class="pwindicator">
                <div class="bar"></div>
                <div class="label"></div>
            </div>
        </div>
        <div class="form-group">
            <label for="password_confirmation" class="form-label">{{__('Password Confirmation')}}</label>
            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" placeholder="Your Confirm Password"  required autocomplete="new-password">
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

        <div class="d-grid">
            <button type="submit" class="btn-login btn btn-primary btn-block mt-2" id="login_button">{{__('Register')}}</button>
        </div>
        <p class="my-4 text-center">{{__("Already' have an account?")}} <a href="{{ route('login') }}" class="text-primary">{{__('Login')}}</a></p>

    </div>
    {{Form::close()}}
@endsection
