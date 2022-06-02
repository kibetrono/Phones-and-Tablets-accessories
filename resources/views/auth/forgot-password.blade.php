@extends('layouts.auth')
@php
    $logo=asset(Storage::url('uploads/logo/'));
    $company_logo=App\Models\Utility::getValByName('company_logo');
@endphp

@section('page-title')
    {{__('Forgot Password')}}
@endsection
        @push('custom-scripts')
            @if(env('RECAPTCHA_MODULE') == 'yes')
                {!! NoCaptcha::renderJs() !!}
            @endif
        @endpush


@section('content')
    <div class="">
        <h2 class="mb-3 f-w-600">{{__('Reset Password')}}</h2>
        <small class="text-muted">{{ __('We will send a link to reset your password') }}</small>
        @if (session('status'))
            <small class="text-muted">{{ session('status') }}</small>
        @endif
    </div>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="">

            <div class="form-group mb-3">
                <label for="email" class="form-label">{{ __('E-Mail') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="{{__('Enter Your Email   ')}}" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                <span class="invalid-feedback" role="alert">
                    <small>{{ $message }}</small>
                </span>
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


            <div class="d-grid">
                <button type="submit" class="btn-login btn btn-primary btn-block mt-2">{{ __('Send Password Reset Link') }}</button>
            </div>
            <p class="my-4 text-center">{{__("Back to")}} <a href="{{ route('login') }}" class="text-primary">{{__('Login')}}</a></p>


        </div>
    {{Form::close()}}
@endsection

