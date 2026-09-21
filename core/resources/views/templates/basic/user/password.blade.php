@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card custom--card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">@lang('Change Password')</h5>
                    <a href="{{ route('user.profile.setting') }}" class="btn btn-sm btn--secondary">
                        <i class="las la-user me-1"></i> @lang('Back to Profile')
                    </a>
                </div>
                <div class="card-body">
                    <form method="post">
                        @csrf
                        <div class="row">
                            <div class="col-12 form-group">
                                <label class="form--label required">@lang('Current Password')</label>
                                <div class="position-relative">
                                    <input type="password" id="current_password" class="form-control form--control" name="current_password" required autocomplete="current-password" style="padding-right: 45px;">
                                    <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#current_password"></span>
                                </div>
                            </div>

                            <div class="col-12 form-group">
                                <label class="form--label required">@lang('New Password')</label>
                                <div class="position-relative">
                                    <input type="password" id="password" class="form-control form--control @if (gs('secure_password')) secure-password @endif" name="password" required autocomplete="new-password" style="padding-right: 45px;">
                                    <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#password"></span>
                                </div>
                            </div>

                            <div class="col-12 form-group">
                                <label class="form--label required">@lang('Confirm New Password')</label>
                                <div class="position-relative">
                                    <input type="password" id="confirm_password" class="form-control form--control" name="password_confirmation" required autocomplete="new-password" style="padding-right: 45px;">
                                    <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash" id="#confirm_password"></span>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <button class="btn btn--base w-100" type="submit">
                                    <i class="las la-check me-1"></i> @lang('Update Password')
                                </button>
                            </div>
                        </div>
                    </form>
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

@push('script')
<script>
    (function($){
        "use strict";
        $(".toggle-password").on('click', function () {
            $(this).toggleClass("fa-eye fa-eye-slash");
            var input = $($(this).attr("id"));
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    })(jQuery);
</script>
@endpush
