@if (@gs('socialite_credentials')->linkedin->status || @gs('socialite_credentials')->facebook->status == Status::ENABLE || @gs('socialite_credentials')->google->status == Status::ENABLE)
    <div class="text-center">
        <span>@lang('OR')</span>
    </div>
@endif

<div class="d-flex social-login flex-wrap gap-3 py-4">
    @if (@gs('socialite_credentials')->google->status == Status::ENABLE)
        <a class="btn btn-google btn-sm flex-grow-1" href="{{ route('user.social.login', 'google') }}">
            <span><i class="lab la-google"></i></span>@lang('Google')
        </a>
    @endif
    @if (@gs('socialite_credentials')->facebook->status == Status::ENABLE)
        <a class="btn btn-facebook btn-sm flex-grow-1" href="{{ route('user.social.login', 'facebook') }}">
            <span><i class="la la-facebook-f"></i></span>@lang('Facebook')
        </a>
    @endif
    @if (@gs('socialite_credentials')->linkedin->status == Status::ENABLE)
        <a class="btn btn-linkedin btn-sm flex-grow-1" href="{{ route('user.social.login', 'linkedin') }}">
            <span><i class="lab la-linkedin-in"></i></span>@lang('LinkedIn')
        </a>
    @endif
</div>
