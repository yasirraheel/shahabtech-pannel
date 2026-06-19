@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="profile-setting-section py-120">
        <div class="container">
            <div class="row justify-content-center gy-4">
                <div class="col-12">
                    <div class="profile-filter d-md-none d-block text-end">
                        <button class="profile-filter__button toggle-profile-sidebar" type="button">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>
                </div>
                <div class="col-xl-10">
                    <div class="profile-setting">
                        @include($activeTemplate . 'partials.user_profile_topbar',['profileMessage' => 'Update your password below'])
                        <div class="row">
                            <div class="col-lg-3 col-md-4">
                                @include($activeTemplate . 'partials.user_profile_sidenav')
                            </div>
                            <div class="col-lg-8 col-md-8">
                                <div class="profile-setting__body">
                                    <form method="post">
                                        @csrf
                                        <div class="row">
                                            <div class="col-sm-12 form-group">
                                                <label class="form--label" for="your-password4"> @lang('Current Password') </label>
                                                <div class="position-relative">
                                                    <input type="password" id="Current" class="form--control exclude" name="current_password" required autocomplete="current-password">
                                                    <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#Current"></span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 form-group">
                                                <label class="form--label" for="your-password4"> @lang('Password') </label>
                                                <div class="position-relative">
                                                    <input type="password" id="password" class="exclude form-control form--control @if (gs('secure_password')) secure-password @endif" name="password" required autocomplete="current-password">
                                                    <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#password"></span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 form-group">
                                                <label class="form--label" for="your-password5">@lang('Confirm Password')</label>
                                                <div class="position-relative">
                                                    <input type="password" id="confirm" class="form-control form--control exclude" name="password_confirmation" required autocomplete="current-password">
                                                    <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#confirm"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-setting__button d-flex justify-content-end">
                                            <button class="btn btn--base "> @lang('Save Changes') </button>
                                        </div>
                                    </form>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
