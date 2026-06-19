@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-5">
                    <div class="card custom--card">
                        <div class="card-body account-form">
                            <div class="mb-4">
                                <p>@lang('To recover your account please provide your email or username to find your account.')</p>
                            </div>
                            <form class="verify-gcaptcha " method="POST" action="{{ route('user.password.email') }}">
                                @csrf
                                <div class="row">

                                    <div class="col-12 form-group">
                                        <label class="form--label">@lang('Email or Username')</label>
                                        <input class="form--control" name="value" type="text"
                                            value="{{ old('value') }}" required autofocus="off">
                                    </div>
                                    @php
                                        $addLabelClass = 'form--label';
                                        $addFormGroupClass = 'col-12';
                                    @endphp

                                    <x-captcha :addLabelClass="$addLabelClass" :addFormGroupClass="$addFormGroupClass" />

                                    <div class="col-12 form-group mb-0">
                                        <button class="btn btn--base  w-100" type="submit">@lang('Submit')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
