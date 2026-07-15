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
    <div id="globalNotificationBanner" class="notification-banner shadow-lg bg-{{ $bannerTheme }}" style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 99999; max-width: 450px; border-radius: 12px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: slideInUp 0.5s ease-out;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="{{ $textColor }} m-0" style="font-size: 16px;"><i class="las la-bell me-2"></i> @lang('Notice')</h6>
            <button type="button" class="{{ $textColor }}" style="background: none; border: none; opacity: 0.8; font-size: 20px; line-height: 1;" onclick="closeNotificationBanner()" aria-label="Close">&times;</button>
        </div>
        <p class="mb-3 {{ $textColor }}" style="font-size: 14px; line-height: 1.5; margin-bottom: 15px;">{!! gs('banner_message') !!}</p>
        @if(gs('banner_cta_text') && gs('banner_cta_link'))
            <div class="text-end">
                <a href="{{ gs('banner_cta_link') }}" target="_blank" class="btn {{ $btnTheme }} btn-sm fw-bold d-inline-flex align-items-center justify-content-center gap-1" style="border-radius: 20px; padding: 6px 15px; font-size: 13px;">
                    {{ gs('banner_cta_text') }} <i class="las la-arrow-right"></i>
                </a>
            </div>
        @endif
    </div>

    @push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var banner = document.getElementById('globalNotificationBanner');
            var bannerClosedAt = localStorage.getItem('bannerClosedAt');
            var now = new Date().getTime();
            
            // If never closed, or closed more than 5 minutes (300000 ms) ago
            if (!bannerClosedAt || (now - parseInt(bannerClosedAt) > 300000)) {
                banner.style.display = 'block';
            }
        });

        function closeNotificationBanner() {
            document.getElementById('globalNotificationBanner').style.display = 'none';
            localStorage.setItem('bannerClosedAt', new Date().getTime());
        }
    </script>
    @endpush
    @endif

    @auth
        @php
            $expiryDate = auth()->user()->expires_at ?: auth()->user()->created_at->addDays(30);
            $daysRemaining = now()->diffInDays($expiryDate, false);
            $contactContent = getContent('contact.content', true)->data_values;
            $whatsappNumber = preg_replace('/[^0-9]/', '', @$contactContent->phone_number);
            $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode("Hello, I would like to renew my account.");
        @endphp
        @if($daysRemaining <= 3)
            <div class="cookies-card hide text-center" id="renewal-card" style="background-color: #ffc107; color: #222; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 999999;">
                <div class="cookies-card__icon" style="background-color: #e0a800; color: #fff;">
                    <i class="las la-exclamation-triangle"></i>
                </div>
                <p class="cookies-card__content mt-4" style="color: #222; font-size: 15px;">
                    <strong style="font-size: 1.2rem; color: #000;">@lang('Notice')</strong><br><br>
                    <strong style="color: #000;">@lang('Dear Valued User,')</strong><br>
                    @if($daysRemaining >= 0)
                        @lang('Your account validity is expiring in') <strong>{{ $daysRemaining }} @lang('days')</strong>.<br>
                    @else
                        @lang('Your account validity is') <strong>@lang('expired')</strong>.<br>
                    @endif
                    @lang('For renewal, please contact us on WhatsApp here:')<br><br>
                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-sm" style="background-color: #25D366; border-color: #25D366; color: white; border-radius: 20px; padding: 8px 20px; font-weight: bold; width: 100%;">
                        <i class="lab la-whatsapp me-1" style="font-size: 1.2rem;"></i> {{ @$contactContent->phone_number }}
                    </a>
                </p>
                <div class="cookies-card__btn mt-3">
                    <a class="btn w-100" id="renewal-okay" href="javascript:void(0)" style="background-color: #222; color: #fff;">@lang('Okay')</a>
                </div>
            </div>
            
            @push('script')
            <script>
                (function($) {
                    "use strict";
                    var renewalCard = $('#renewal-card');
                    var lastClosed = localStorage.getItem('renewalClosedAt');
                    var now = new Date().getTime();
                    
                    // If not closed in the last 3 hours (10800000 ms)
                    if (!lastClosed || (now - parseInt(lastClosed) > 10800000)) {
                        setTimeout(function() {
                            renewalCard.removeClass('hide');
                        }, 2000);
                    }
                    
                    $('#renewal-okay').on('click', function() {
                        renewalCard.addClass('d-none');
                        localStorage.setItem('renewalClosedAt', new Date().getTime());
                    });
                })(jQuery);
            </script>
            @endpush
        @endif
    @endauth

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
