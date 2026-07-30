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
    <div id="globalNotificationBanner" class="notification-banner shadow-lg bg-{{ $bannerTheme }}" style="display: none; position: fixed; bottom: 0; left: 0; width: 100%; z-index: 99999; padding: 10px 0; box-shadow: 0 -5px 25px rgba(0,0,0,0.15); animation: slideInUp 0.5s ease-out;">
        <div class="container position-relative">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 pe-4">
                <div class="flex-grow-1">
                    <h6 class="{{ $textColor }} mb-1" style="font-size: 15px;"><i class="las la-bell me-2"></i> @lang('Notice')</h6>
                    <div class="{{ $textColor }}" style="font-size: 13px; line-height: 1.4;">
                        {!! gs('banner_message') !!}
                    </div>
                </div>
                @if(gs('banner_cta_text') && gs('banner_cta_link'))
                    @php
                        $ctaLink = gs('banner_cta_link');
                        if (auth()->check()) {
                            $ctaLink = str_replace('[username]', auth()->user()->username, $ctaLink);
                            $ctaLink = str_replace('[email]', auth()->user()->email, $ctaLink);
                            $ctaLink = str_replace(urlencode('[username]'), urlencode(auth()->user()->username), $ctaLink);
                            $ctaLink = str_replace(urlencode('[email]'), urlencode(auth()->user()->email), $ctaLink);
                        }
                    @endphp
                    <div class="flex-shrink-0 mt-2 mt-md-0">
                        <a href="{{ $ctaLink }}" target="_blank" class="btn {{ $btnTheme }} btn-sm fw-bold d-inline-flex align-items-center justify-content-center gap-1" style="border-radius: 20px; padding: 8px 15px; font-size: 13px; white-space: nowrap;">
                            {{ gs('banner_cta_text') }} <i class="las la-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </div>
            <button type="button" class="{{ $textColor }} position-absolute" style="top: -2px; right: 10px; background: none; border: none; opacity: 0.8; font-size: 24px; line-height: 1; padding: 0;" onclick="closeNotificationBanner()" aria-label="Close">&times;</button>
        </div>
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
            $daysRemaining = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($expiryDate)->startOfDay(), false);
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

    @auth
        @php
            $minExtVersion = gs('min_extension_version') ?: '1.9.6';
            $forceExtUpdate = (bool) gs('force_extension_update');
            $extDownloadUrl = getExtensionDownloadUrl();
        @endphp
        <!-- Extension Update Modal on Web Panel -->
        <div class="modal fade custom--modal" id="panelExtensionUpdateModal" tabindex="-1" role="dialog" aria-labelledby="panelExtensionUpdateTitle" aria-hidden="true" @if($forceExtUpdate) data-bs-backdrop="static" data-bs-keyboard="false" @endif>
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title d-flex align-items-center text-warning" id="panelExtensionUpdateTitle">
                            <i class="las la-exclamation-triangle me-2 fs-4"></i>
                            @if($forceExtUpdate)
                                @lang('Action Required: Extension Update')
                            @else
                                @lang('Extension Update Available')
                            @endif
                        </h5>
                        @if(!$forceExtUpdate)
                            <button type="button" class="btn-close modal-icon" data-bs-dismiss="modal" aria-label="Close"><i class="las la-times"></i></button>
                        @endif
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                @lang('Required Version'): <strong>v{{ $minExtVersion }}</strong>
                            </span>
                        </div>
                        @if($forceExtUpdate)
                            <p class="text-white fs-15 mb-0">
                                @lang('Your browser extension is outdated. The administrator has required an update to continue accessing your assigned accounts seamlessly.')
                            </p>
                        @else
                            <p class="text-white fs-15 mb-0">
                                @lang('A new version of the WeMate Chrome Extension (v')<strong>{{ $minExtVersion }}</strong>@lang(') is available. Please update to enjoy the latest features.')
                            </p>
                        @endif
                    </div>
                    <div class="modal-footer border-0 pt-0 d-flex gap-2">
                        <a href="{{ $extDownloadUrl }}" target="_blank" id="panelUpdateDownloadBtn" class="btn btn--base flex-grow-1">
                            <i class="las la-download me-1"></i> @lang('Download Extension Update')
                        </a>
                        @if(!$forceExtUpdate)
                            <button type="button" class="btn btn--secondary flex-grow-1" id="panelUpdateSnoozeBtn" data-bs-dismiss="modal">
                                @lang('Snooze (6 Hours)')
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @push('script')
        <script>
            (function($) {
                "use strict";

                var requiredVer = "{{ $minExtVersion }}";
                var isStrictForce = {{ $forceExtUpdate ? 'true' : 'false' }};
                var SNOOZE_MS = 6 * 60 * 60 * 1000; // 6 Hours

                function isOutdated(installed, required) {
                    if (!required) return false;
                    var p1 = installed.split('.').map(Number);
                    var p2 = required.split('.').map(Number);
                    for (var i = 0; i < Math.max(p1.length, p2.length); i++) {
                        var n1 = p1[i] || 0;
                        var n2 = p2[i] || 0;
                        if (n1 < n2) return true;
                        if (n1 > n2) return false;
                    }
                    return false;
                }

                function checkPanelExtensionUpdate() {
                    var extInstalledMeta = $('meta[name="shahabtech-extension-installed"]').length > 0 || 
                                           $('meta[name="extension-installed"]').length > 0 ||
                                           $('meta[name="wemate-extension-installed"]').length > 0;
                    
                    var installedVer = $('meta[name="extension-version"]').attr('content') || '1.0.0';

                    if (!extInstalledMeta || isOutdated(installedVer, requiredVer)) {
                        var modal = $('#panelExtensionUpdateModal');
                        if (!modal.length) return;

                        if (isStrictForce) {
                            modal.modal('show');
                        } else {
                            var lastSnooze = localStorage.getItem('wemate_panel_update_snooze') || 0;
                            var now = new Date().getTime();
                            if (now - parseInt(lastSnooze) > SNOOZE_MS) {
                                modal.modal('show');
                            }
                        }

                        $('#panelUpdateSnoozeBtn').on('click', function() {
                            localStorage.setItem('wemate_panel_update_snooze', new Date().getTime());
                        });
                    }
                }

                setTimeout(checkPanelExtensionUpdate, 1500);
            })(jQuery);
        </script>
        @endpush
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
