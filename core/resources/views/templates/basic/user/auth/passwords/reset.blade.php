@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="py-120">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-4">
                <div class="card custom--card">
                    <div class="card-body account-form">
                        <div class="mb-4">
                            <p>@lang('Your account is verified successfully. Now you can change your password. Please enter a strong password and don\'t share it with anyone.')</p>
                        </div>
                        <form method="POST" action="{{ route('user.password.update') }}">
                            @csrf
                            <input name="email" type="hidden" value="{{ $email }}">
                            <input name="token" type="hidden" value="{{ $token }}">

                            <div class="row p-0">
                                <div class="col-12 form-group">
                                    <label class="form--label">@lang('Password')</label>
                                    <div class="position-relative">
                                        <input class="form--control @if (gs('secure_password')) secure-password @endif" id="password" name="password" type="password" required>
                                        <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#password"></span>
                                    </div>
                                </div>
                                <div class="col-12 form-group">
                                    <label class="form--label">@lang('Confirm Password')</label>
                                    <div class="position-relative">
                                        <input class="form--control" id="confirm-password" name="password_confirmation" type="password" required>
                                        <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#confirm-password"></span>
                                    </div>
                                </div>
                                <div class="col-12 form-group mb-0">
                                    <button class="btn btn--base  w-100" type="submit"> @lang('Submit')</button>
                                </div>
                            </div>

                        </form>
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
