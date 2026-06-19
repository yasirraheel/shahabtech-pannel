@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $registerContent = getContent('register.content', true);
    @endphp
  @if(gs('registration'))
    <section class="account-section bg-img" style="background-image: url({{ getImage('assets/images/frontend/register/' . $registerContent->data_values->background_image, '1920x1025') }})">
        <div class="account-inner">
            <div class="container">
               
                <div class="row justify-content-center justify-content-xl-start">
                    <div class="col-xl-6 col-xxl-5 col-md-8">
                        <div class="account-form">
                            <div class="account-form__content">
                                <a class="account-form__logo" href="{{ route('home') }}">
                                    <img src="{{ siteLogo() }}" alt="Logo">
                                </a>
                                <p class="account-form__desc"> {{ __(@$registerContent->data_values->heading) }} </p>
                            </div>
                            <form class="verify-gcaptcha" action="{{ route('user.register') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6 form-group">
                                        <div class="form--group">
                                            <label class="form--label">@lang('First Name')</label>
                                            <input class="form--control" name="firstname" type="text" value="{{ old('firstname') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <div class="form--group">
                                            <label class="form--label">@lang('Last Name')</label>
                                            <input class="form--control" name="lastname" type="text" value="{{ old('lastname') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <div class="form--group">
                                            <label class="form--label">@lang('E-Mail Address')</label>
                                            <input class="form--control checkUser" name="email" type="email" value="{{ old('email', @request()->email) }}" required @readonly(@$startEmail)>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <label class="form--label" for="your-password">@lang('Password')</label>
                                        <div class="position-relative">
                                            <input class="form-control form--control @if (gs('secure_password')) secure-password @endif" id="password" name="password" type="password" required>
                                            <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#password"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <label class="form--label" for="your-password">@lang('Confirm Password')</label>
                                        <div class="position-relative">
                                            <input class="form-control form--control" id="confirm-password" name="password_confirmation" type="password" required>
                                            <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#confirm-password"></span>
                                        </div>
                                    </div>

                                    @php
                                        $addLabelClass = 'form--label';
                                        $addFormGroupClass = 'col-sm-12';
                                    @endphp

                                    <x-captcha :addLabelClass="$addLabelClass" :addFormGroupClass="$addFormGroupClass" />

                                    @if (gs('agree'))
                                    @php
                                        $policyPages = getContent('policy_pages.element', false, orderById:true);
                                    @endphp

                                        <div class="col-sm-12 form-group form--check">
                                            <input class="form-check-input" id="agree" name="agree" type="checkbox" @checked(old('agree')) required>
                                            <label class="ps-3" for="agree">@lang('I agree with')</label>&nbsp;<span>
                                                @foreach ($policyPages as $policy)
                                                    <a href="{{ route('policy.pages', [slug($policy->data_values->title), $policy->id]) }}" target="_blank">{{ __($policy->data_values->title) }}</a>
                                                    @if (!$loop->last)
                                                        ,
                                                    @endif
                                                @endforeach
                                            </span>
                                        </div>
                                    @endif

                                    <div class="col-sm-12 form-group">
                                        <button class="btn btn--base w-100" type="submit">@lang('Register')</button>
                                    </div>

                                    @include($activeTemplate . 'partials.social_login')

                                    <div class="col-sm-12">
                                        <div class="have-account text-center">
                                            <p class="have-account__text">@lang('Already have an account?') <a class="have-account__link text--base" href="{{ route('user.login') }}">@lang('Login Here ')</a></p>
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

    <div class="modal custom--modal fade" id="existModalCenter" role="dialog" aria-labelledby="existModalCenterTitle" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <button class="btn-close modal-icon" data-bs-dismiss="modal" type="button" aria-label="Close"> <i class="las la-times"></i></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-0">@lang('You already have an account please Login ')</h6>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-dark btn-sm" data-bs-dismiss="modal" type="button">@lang('Close')</button>
                    <a class="btn btn--base btn-sm" href="{{ route('user.login') }}">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>

    @else
    <div class="container">
        @include($activeTemplate.'partials.registration_disabled')
    </div>
@endif
@endsection

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
@push('script')
    <script>
        "use strict";
        (function($) {

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                var data = {
                    email: value,
                    _token: token
                }

                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $('#existModalCenter').modal('show');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
