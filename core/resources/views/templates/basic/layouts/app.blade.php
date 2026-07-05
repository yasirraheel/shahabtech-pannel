<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ gs()->siteName(__($pageTitle)) }}</title>
    @include('partials.seo')
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('assets/global/css/select2.min.css')}}">

    <link href="{{ asset('assets/global/css/line-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset($activeTemplateTrue . 'css/main.css') }}" rel="stylesheet">
    <link href="{{ asset($activeTemplateTrue . 'css/custom.css') }}" rel="stylesheet">

    <link href="{{ asset($activeTemplateTrue . 'css/color.php') }}?color={{ gs('base_color') }}" rel="stylesheet">

    @stack('style-lib')

    @stack('style')
</head>

@php echo loadExtension('google-analytics') @endphp

<body>

    <div class="preloader">
        <div class="loader-p"></div>
    </div>

    <div class="body-overlay"></div>

    <div class="sidebar-overlay"></div>

    <a class="scroll-top"><i class="fas fa-angle-double-up"></i></a>

    @if(gs('banner_status') && gs('banner_message'))
    @php
        $bannerTheme = gs('banner_color') ?: 'primary';
        $textColor = in_array($bannerTheme, ['warning', 'info']) ? 'text-dark' : 'text-white';
        $btnTheme = in_array($bannerTheme, ['warning', 'info']) ? 'btn-dark' : 'btn-light';
    @endphp
    <div class="notification-banner shadow-lg bg-{{ $bannerTheme }}" style="position: fixed; bottom: 30px; right: 30px; z-index: 99999; max-width: 450px; border-radius: 12px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: slideInUp 0.5s ease-out;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="{{ $textColor }} m-0" style="font-size: 16px;"><i class="las la-bell me-2"></i> @lang('Notice')</h6>
            <button type="button" class="{{ $textColor }}" style="background: none; border: none; opacity: 0.8; font-size: 20px; line-height: 1;" onclick="this.parentElement.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
        <p class="mb-3 {{ $textColor }}" style="font-size: 14px; line-height: 1.5; margin-bottom: 15px;">{!! gs('banner_message') !!}</p>
        @if(gs('banner_cta_text') && gs('banner_cta_link'))
            <div class="text-end">
                <a href="{{ gs('banner_cta_link') }}" target="_blank" class="btn {{ $btnTheme }} btn-sm fw-bold" style="border-radius: 20px; padding: 5px 15px; font-size: 13px;">
                    {{ gs('banner_cta_text') }} <i class="las la-arrow-right ms-1"></i>
                </a>
            </div>
        @endif
    </div>
    @endif

    @yield('panel')

    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{asset('assets/global/js/select2.min.js')}}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/main.js') }}"></script>

    @stack('script-lib')

    @include('partials.notify')

    @php echo loadExtension('tawk-chat') @endphp

    @if (gs('pn'))
        @include('partials.push_script')
    @endif

    @stack('script')

    <script>
        (function($) {
            "use strict";
            $(".langSel").on("click", function() {
                var code = $(this).data('code');
                window.location.href = "{{ route('home') }}/change/" + code;
            });

            var inputElements = $('[type=text],select,textarea');
            $.each(inputElements, function(index, element) {
                element = $(element);
                element.closest('.form-group').find('label').attr('for', element.attr('name'));
                element.attr('id', element.attr('name'))
            });

            $.each($('input:not([type=checkbox]):not([type=hidden]), select, textarea'), function (i, element) {
                var elementType = $(element);
                if (elementType.attr('type') != 'checkbox') {
                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').addClass('required');
                    }
                }

            });

           


        })(jQuery);
    </script>
</body>

</html>
