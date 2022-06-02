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

@section('auth-lang')
    <select class="btn btn-primary my-1 me-2 " onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);" id="language">
        @foreach(Utility::languages() as $language)
            <option @if($lang == $language) selected @endif value="{{ route('customer.change.langPass',$language) }}">{{Str::upper($language)}}</option>
        @endforeach
    </select>
@endsection


@section('content')
    <div class="">
        <h2 class="mb-3 f-w-600">{{__('Reset Password')}}</h2>
        <small class="text-muted">{{ __('We will send a link to reset your password') }}</small>
        @if (session('status'))
            <small class="text-muted">{{ session('status') }}</small>
        @endif
    </div>
    <form method="POST" action="{{ route('customer.password.email') }}">
        @csrf
        <div class="">

            <div class="form-group mb-3">
                <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
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
            <p class="my-4 text-center">{{__("Back to")}} <a href="{{ route('customer.login') }}" class="text-primary">{{__('Login')}}</a></p>

        </div>
    {{Form::close()}}
@endsection

