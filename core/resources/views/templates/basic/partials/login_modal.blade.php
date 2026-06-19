    {{-- LOGIN  MODAL --}}
    <div class="modal fade custom--modal" id="loginModal">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-block">
                    <h3 class="product-details__right-title acc_title"> @lang('Login Now') </h3>
                    <a class="product-details__right-link acc_link" href=""> </a>
                    <button class="btn-close modal-icon" data-bs-dismiss="modal" type="button" aria-label="Close"> 
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="account-form">
                        <form class="verify-gcaptcha" method="POST" action="{{ route('user.login') }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <div class="form--group">
                                        <label class="form--label">@lang('Username or Email')</label>
                                        <input class="form--control" name="username" type="text"
                                            value="{{ old('username') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-12 form-group">
                                    <div class="d-flex justify-content-between">
                                        <label class="form--label" for="your-password3">@lang('Password')</label>
                                        <a class="forgot-password fs-14" href="{{ route('user.password.request') }}">
                                            @lang('Forgot your password?') </a>
                                    </div>
                                    <div class="position-relative">
                                        <input class="form-control form--control" id="your-password3" name="password"
                                            type="password">
                                        <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash"
                                            id="#your-password3"></span>
                                    </div>
                                    <input name="listing_title" type="hidden">
                                    <input name="listing_id" type="hidden">
                                </div>
                                @php
                                    $addLabelClass = 'form--label';
                                @endphp
                                <div class="col-12">
                                    <x-captcha :addLabelClass="$addLabelClass" />
                                </div>
                                <div class="col-sm-12 form-group">
                                    <div class="form-check form--check">
                                        <input class="form-check-input" id="remember" name="remember" type="checkbox"
                                            {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            @lang('Remember Me')
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-12 form-group">
                                    <button class="btn btn--base  w-100" type="submit"> @lang('Login')
                                    </button>
                                </div>
                                @php 
                                    $credentials = gs('socialite_credentials');
                                @endphp
                                @if ($credentials->google->status == Status::ENABLE || $credentials->facebook->status == Status::ENABLE || $credentials->linkedin->status == Status::ENABLE)
                                    <p class="text-center">
                                        @lang('OR')
                                    </p>
                                    <div class="d-flex social-login flex-wrap gap-3 py-4">
                                        @if ($credentials->facebook->status == Status::ENABLE)
                                            <a class="btn btn-facebook btn-sm flex-grow-1"
                                                href="{{ route('user.social.login', 'facebook') }}">
                                                <span><i class="la la-facebook"></i></span>@lang('Facebook')
                                            </a>
                                        @endif
                                        @if ($credentials->google->status == Status::ENABLE)
                                            <a class="btn btn-google btn-sm flex-grow-1"
                                                href="{{ route('user.social.login', 'google') }}">
                                                <span><i class="lab la-google"></i></span>@lang('Google')
                                            </a>
                                        @endif
                                        @if ($credentials->linkedin->status == Status::ENABLE)
                                            <a class="btn btn-linkedin btn-sm flex-grow-1"
                                                href="{{ route('user.social.login', 'linkedin') }}">
                                                <span><i class="lab la-linkedin-in"></i></span>@lang('LinkedIn')
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                <div class="col-sm-12">
                                    <div class="have-account text-center">
                                        <p class="have-account__text"> @lang('Don\'t have any account') <a
                                                class="have-account__link text--base"
                                                href="{{ route('user.register') }}"> @lang('Register') </a></p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--============= LOGIN MODAL END ============= -->
