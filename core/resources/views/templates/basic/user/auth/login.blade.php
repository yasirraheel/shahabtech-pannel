@extends($activeTemplate . 'layouts.frontend')

@section('content')
    @php
        $loginContent = getContent('login.content', true);
    @endphp
    <section class="account-section bg-img" style="background-image: url({{ getImage('assets/images/frontend/login/' . @$loginContent->data_values->background_image, '1920x1025') }})">
        <div class="account-inner">
            <div class="container">
                <div class="row justify-content-center justify-content-xl-start">
                    <div class="col-xl-5 col-md-8 col-lg-6">
                        <div class="account-form">
                            <div class="account-form__content">
                                <a class="account-form__logo" href="{{ route('home') }}">
                                    <img src="{{ siteLogo() }}" alt="Logo">
                                </a>
                                <p class="account-form__desc"> {{ __(@$loginContent->data_values->heading) }} </p>
                            </div>
                            <form class="verify-gcaptcha" method="POST" action="{{ route('user.login') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <div class="form--group">
                                            <label class="form--label">@lang('Username or Email')</label>
                                            <input class="form--control" name="username" type="text" value="{{ old('username') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group">
                                        <div class="d-flex justify-content-between">
                                            <label class="form--label" for="your-password3">@lang('Password')</label>
                                            <a class="forgot-password fs-14" href="{{ route('user.password.request') }}">
                                                @lang('Forgot your password?') </a>
                                        </div>
                                        <div class="position-relative">
                                            <input class="form-control form--control" id="your-password3" name="password" type="password">
                                            <span class="password-show-hide fa toggle-password fa-eye-slash" id="#your-password3"></span>
                                        </div>
                                    </div>

                                    @php
                                        $addLabelClass = 'form--label';
                                        $addFormGroupClass = 'col-sm-12';
                                    @endphp

                                    <x-captcha :addLabelClass="$addLabelClass" :addFormGroupClass="$addFormGroupClass" />

                                    <div class="col-sm-12 form-group">
                                        <div class="form-check form--check">
                                            <input class="form-check-input" id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remember">
                                                @lang('Remember Me')
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-12 form-group">
                                        <button class="btn btn--base w-100" type="submit"> @lang('Login')
                                        </button>
                                    </div>

                                    @include($activeTemplate . 'partials.social_login')

                                    <div class="col-sm-12">
                                        <div class="have-account text-center">
                                            <p class="have-account__text"> @lang('Don\'t have any account?') <a
                                                   class="have-account__link text--base" href="{{ route('user.register') }}"> @lang('Register Here') </a></p>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
