@extends($activeTemplate . 'layouts.app')
@section('panel')
    @stack('fbComment')

    @if (!request()->routeIs(['user.login', 'user.register', 'get.started']))
        @include($activeTemplate . 'partials.header')
    @endif

    @if (!request()->routeIs(['user.login', 'user.register', 'home', 'ticket*', 'get.started', 'user.data', 'user.authorization']))
        @include($activeTemplate . 'partials.breadcrumb')
    @endif

    @yield('content')

    @if (!request()->routeIs(['user.login', 'user.register', 'get.started']))
        @include($activeTemplate . 'partials.footer')
    @endif

    @php
        $cookie = App\Models\Frontend::where('data_keys', 'cookie.data')->first();
    @endphp

    @if ($cookie->data_values->status == Status::ENABLE && !\Cookie::get('gdpr_cookie'))
        <!-- cookies dark version start -->
        <div class="cookies-card hide text-center">
            <div class="cookies-card__icon bg--base">
                <i class="las la-cookie-bite"></i>
            </div>
            <p class="cookies-card__content mt-4">{{ $cookie->data_values->short_desc }} <a href="{{ route('cookie.policy') }}" target="_blank">@lang('learn more')</a></p>
            <div class="cookies-card__btn mt-4">
                <a class="btn btn--base w-100 policy" href="javascript:void(0)">@lang('Allow')</a>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.policy').on('click', function() {
                $.get('{{ route('cookie.accept') }}', function(response) {
                    $('.cookies-card').addClass('d-none');
                });
            });

            setTimeout(function() {
                $('.cookies-card').removeClass('hide')
            }, 2000);

        })(jQuery);
    </script>
@endpush
