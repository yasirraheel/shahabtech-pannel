<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ gs()->siteName(__($pageTitle ?? 'User Portal')) }}</title>
    @include('partials.seo')

    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/bootstrap-toggle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}">
    <link href="{{ asset($activeTemplateTrue . 'css/main.css') }}" rel="stylesheet">
    <link href="{{ asset($activeTemplateTrue . 'css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset($activeTemplateTrue . 'css/color.php') }}?color={{ gs('base_color') }}" rel="stylesheet">

    @stack('style-lib')

    <style>
        /* Complete Dark Theme for User Panel (Matching Frontend Dark Background) */
        body {
            background-color: #0b0f19 !important;
            color: #cbd5e1 !important;
            font-family: 'Poppins', sans-serif;
        }
        .page-wrapper {
            background-color: #0b0f19 !important;
            min-height: 100vh;
        }
        .body-wrapper {
            background-color: #0b0f19 !important;
        }
        .bodywrapper__inner {
            background-color: #0b0f19 !important;
            padding: 25px 20px;
        }
        .sidebar {
            background-color: #070a12 !important;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            z-index: 1000;
        }
        .sidebar__inner {
            background-color: #070a12 !important;
        }
        .navbar-wrapper {
            background-color: #070a12 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        /* Headings & Texts */
        .page-title, h1, h2, h3, h4, h5, h6 {
            color: #ffffff !important;
        }
        .text-dark, .text--dark {
            color: #f8fafc !important;
        }
        .text-muted {
            color: #94a3b8 !important;
        }
        label, .form-label, .required::after, .form-group label {
            color: #f8fafc !important;
        }
        small, .small {
            color: #94a3b8 !important;
        }

        /* Dark Cards & Containers */
        .card, .card.custom--card {
            background-color: #111827 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #e2e8f0 !important;
            border-radius: 8px;
            width: 100%;
        }
        .card-header, .card.custom--card .card-header {
            background-color: #162032 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }
        .card-footer {
            background-color: #162032 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Dark Form Inputs */
        .form-control, .form-select, select.form-control, textarea.form-control {
            background-color: #0b0f19 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #f8fafc !important;
        }
        .form-control:focus, .form-select:focus {
            background-color: #0f172a !important;
            border-color: #4634ff !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.25rem rgba(70, 52, 255, 0.25) !important;
        }
        .form-control[readonly], .form-control:disabled {
            background-color: #162032 !important;
            color: #94a3b8 !important;
        }
        .input-group-text {
            background-color: #1e293b !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #e2e8f0 !important;
        }

        /* Input Group Row Fix */
        .input-group {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            width: 100% !important;
        }
        .input-group > .form--control,
        .input-group > .form-control {
            flex: 1 1 auto !important;
            width: 1% !important;
            min-width: 0 !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            height: 51px !important;
        }
        .input-group > .btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 51px !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }
        .input-group > .btn:not(:last-child) {
            border-radius: 0 !important;
        }
        .input-group > .btn:last-child {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        /* Alerts in Dark Theme */
        .alert-warning, .alert--warning {
            background-color: rgba(234, 179, 8, 0.12) !important;
            border: 1px solid rgba(234, 179, 8, 0.35) !important;
            color: #fef08a !important;
        }
        .alert-warning *, .alert--warning * {
            color: #fef08a !important;
        }
        .alert-warning strong, .alert--warning strong {
            color: #ffffff !important;
        }
        .alert-danger, .alert--danger {
            background-color: rgba(239, 68, 68, 0.12) !important;
            border: 1px solid rgba(239, 68, 68, 0.35) !important;
            color: #fca5a5 !important;
        }
        .alert-info, .alert--info {
            background-color: rgba(59, 130, 246, 0.12) !important;
            border: 1px solid rgba(59, 130, 246, 0.35) !important;
            color: #93c5fd !important;
        }

        /* High Contrast Notice Banner */
        #globalNotificationBanner,
        #globalNotificationBanner * {
            color: #f8fafc !important;
        }
        #globalNotificationBanner p,
        #globalNotificationBanner span,
        #globalNotificationBanner div,
        #globalNotificationBanner li,
        #globalNotificationBanner em,
        #globalNotificationBanner font {
            color: #f8fafc !important;
        }
        #globalNotificationBanner strong,
        #globalNotificationBanner b,
        #globalNotificationBanner h1,
        #globalNotificationBanner h2,
        #globalNotificationBanner h3,
        #globalNotificationBanner h4,
        #globalNotificationBanner h5,
        #globalNotificationBanner h6 {
            color: #ffffff !important;
        }
        #globalNotificationBanner a.btn {
            color: #000000 !important;
            background-color: var(--base-color, #4634ff) !important;
            font-weight: 700 !important;
        }
        #globalNotificationBanner a.btn * {
            color: #000000 !important;
        }

        /* Dark Tables */
        .table {
            color: #e2e8f0 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .table th {
            background-color: #162032 !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .table td {
            background-color: #111827 !important;
            color: #cbd5e1 !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        /* Dark Modals */
        .modal-content {
            background-color: #111827 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #e2e8f0 !important;
        }
        .modal-header, .modal-footer {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .modal-title {
            color: #ffffff !important;
        }
        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Dropdowns */
        .dropdown-menu {
            background-color: #111827 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
        }
        .dropdown-menu__item, .dropdown-item {
            color: #e2e8f0 !important;
        }
        .dropdown-menu__item:hover, .dropdown-item:hover {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }
        .dropdown-menu__header, .dropdown-menu__footer {
            background-color: #162032 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }

        /* Layout spacing fixes */
        .py-120 {
            padding-top: 10px !important;
            padding-bottom: 25px !important;
        }
        .container {
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .profile-setting-section, .dashboard-section {
            padding-top: 5px !important;
            padding-bottom: 25px !important;
        }

        /* Select2 dark override */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            background-color: #0b0f19 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }
        .select2-dropdown {
            background-color: #111827 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4634ff !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #ffffff !important;
        }
    </style>

    @stack('style')
</head>

<body>

    {{-- Main Page Wrapper with Dark Admin Style --}}
    <div class="page-wrapper default-version">
        @include($activeTemplate . 'partials.user_sidenav')
        @include($activeTemplate . 'partials.user_topnav')

        <div class="container-fluid px-3 px-sm-0">
            <div class="body-wrapper">
                <div class="bodywrapper__inner">
                    {{-- Global Notification Banner (Gracefully placed at top of content) --}}
                    @if(gs('banner_status') && gs('banner_message'))
                    @php
                        $ctaLink = gs('banner_cta_link');
                        if (auth()->check()) {
                            $ctaLink = str_replace('[username]', auth()->user()->username, $ctaLink);
                            $ctaLink = str_replace('[email]', auth()->user()->email, $ctaLink);
                            $ctaLink = str_replace(urlencode('[username]'), urlencode(auth()->user()->username), $ctaLink);
                            $ctaLink = str_replace(urlencode('[email]'), urlencode(auth()->user()->email), $ctaLink);
                        }
                    @endphp
                    <div id="globalNotificationBanner" class="alert custom--card mb-4 p-3 position-relative" style="display: none; background: #162032 !important; border: 1px solid rgba(99, 102, 241, 0.35) !important; border-left: 4px solid var(--base-color, #4634ff) !important; border-radius: 8px;">
                        <div class="d-flex align-items-start justify-content-between flex-wrap flex-md-nowrap gap-3 pe-4">
                            <div class="d-flex align-items-start gap-3">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.15); display: flex; align-items: center; justify-content: center; color: var(--base-color, #4634ff); flex-shrink: 0;">
                                    <i class="las la-bullhorn" style="font-size: 20px;"></i>
                                </div>
                                <div>
                                    <h6 class="text-white mb-1" style="font-size: 14px; font-weight: 600;">@lang('Notice')</h6>
                                    <div style="color: #cbd5e1 !important; font-size: 13px; line-height: 1.5;">
                                        {!! gs('banner_message') !!}
                                    </div>
                                </div>
                            </div>
                            @if(gs('banner_cta_text') && gs('banner_cta_link'))
                                <div class="flex-shrink-0 mt-2 mt-md-0 align-self-center">
                                    <a href="{{ $ctaLink }}" target="_blank" class="btn btn--base btn-sm text-nowrap">
                                        {{ gs('banner_cta_text') }} <i class="las la-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn-close position-absolute" style="top: 12px; right: 12px; filter: invert(1); opacity: 0.7; font-size: 11px;" onclick="closeNotificationBanner()" aria-label="Close"></button>
                    </div>

                    @push('script')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var banner = document.getElementById('globalNotificationBanner');
                            var bannerClosedAt = localStorage.getItem('bannerClosedAt');
                            var now = new Date().getTime();
                            if (!bannerClosedAt || (now - parseInt(bannerClosedAt) > 300000)) {
                                if(banner) banner.style.display = 'block';
                            }
                        });

                        function closeNotificationBanner() {
                            var banner = document.getElementById('globalNotificationBanner');
                            if(banner) banner.style.display = 'none';
                            localStorage.setItem('bannerClosedAt', new Date().getTime());
                        }
                    </script>
                    @endpush
                    @endif

                    @include($activeTemplate . 'partials.user_breadcrumb')
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @auth
        @php
            $expiryDate = auth()->user()->expires_at ?: auth()->user()->created_at->addDays(30);
            $daysRemaining = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($expiryDate)->startOfDay(), false);
            $contactContent = getContent('contact.content', true)->data_values;
            $whatsappNumber = preg_replace('/[^0-9]/', '', @$contactContent->phone_number);
            $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode("Hello, I would like to renew my account.");
            $minExtVersion = gs('min_extension_version') ?: '1.9.6';
            $forceExtUpdate = (bool) gs('force_extension_update');
            $extDownloadUrl = getExtensionDownloadUrl();
        @endphp

        {{-- WhatsApp Renewal Popup Card --}}
        @if($daysRemaining <= 3)
            <div class="cookies-card hide text-center" id="renewal-card" style="position: fixed; bottom: 20px; right: 20px; max-width: 380px; background-color: #ffc107; color: #222; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 999999; border-radius: 12px; padding: 20px;">
                <div class="cookies-card__icon" style="background-color: #e0a800; color: #fff; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="las la-exclamation-triangle" style="font-size: 24px;"></i>
                </div>
                <p class="cookies-card__content mt-3 mb-3" style="color: #222; font-size: 14px; line-height: 1.5;">
                    <strong style="font-size: 1.15rem; color: #000;">@lang('Notice')</strong><br>
                    <strong style="color: #000;">@lang('Dear Valued User,')</strong><br>
                    @if($daysRemaining >= 0)
                        @lang('Your account validity is expiring in') <strong>{{ $daysRemaining }} @lang('days')</strong>.<br>
                    @else
                        @lang('Your account validity is') <strong>@lang('expired')</strong>.<br>
                    @endif
                    @lang('For renewal, please contact us on WhatsApp here:')<br><br>
                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-sm w-100" style="background-color: #25D366; border-color: #25D366; color: white; border-radius: 20px; padding: 8px 20px; font-weight: bold;">
                        <i class="lab la-whatsapp me-1" style="font-size: 1.2rem;"></i> {{ @$contactContent->phone_number }}
                    </a>
                </p>
                <div class="cookies-card__btn">
                    <a class="btn w-100 btn-sm" id="renewal-okay" href="javascript:void(0)" style="background-color: #222; color: #fff; border-radius: 20px;">@lang('Okay')</a>
                </div>
            </div>
            
            @push('script')
            <script>
                (function($) {
                    "use strict";
                    var renewalCard = $('#renewal-card');
                    var lastClosed = localStorage.getItem('renewalClosedAt');
                    var now = new Date().getTime();
                    
                    if (!lastClosed || (now - parseInt(lastClosed) > 10800000)) {
                        setTimeout(function() {
                            renewalCard.removeClass('hide').fadeIn();
                        }, 2000);
                    }
                    
                    $('#renewal-okay').on('click', function() {
                        renewalCard.fadeOut();
                        localStorage.setItem('renewalClosedAt', new Date().getTime());
                    });
                })(jQuery);
            </script>
            @endpush
        @endif

        {{-- Extension Update Modal on Web Panel --}}
        <div class="modal fade custom--modal" id="panelExtensionUpdateModal" tabindex="-1" role="dialog" aria-labelledby="panelExtensionUpdateTitle" aria-hidden="true" @if($forceExtUpdate) data-bs-backdrop="static" data-bs-keyboard="false" @endif>
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="background: #111827 !important; border: 1px solid rgba(255,255,255,0.1) !important; color: #fff;">
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
                            <button type="button" class="btn-close modal-icon" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                @lang('A new version of the') {{ __(gs('site_name')) }} @lang('Extension (v')<strong>{{ $minExtVersion }}</strong>@lang(') is available. Please update to enjoy the latest features.')
                            </p>
                        @endif
                    </div>
                    <div class="modal-footer border-0 pt-0 d-flex gap-2">
                        <a href="{{ $extDownloadUrl }}" target="_blank" id="panelUpdateDownloadBtn" class="btn btn--primary flex-grow-1">
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
    @endauth

    {{-- Core Scripts --}}
    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/magnific-popup.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/bootstrap-toggle.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>

    @include('partials.notify')
    @stack('script-lib')

    @auth
    <script>
        (function($) {
            "use strict";

            var requiredVer = "{{ $minExtVersion }}";
            var isStrictForce = {{ $forceExtUpdate ? 'true' : 'false' }};
            var SNOOZE_MS = 6 * 60 * 60 * 1000;

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
                var extInstalledMeta = $('meta[name="toolsbydcx-extension-installed"]').length > 0 ||
                                       $('meta[name="wemate-extension-installed"]').length > 0 ||
                                       $('meta[name="shahabtech-extension-installed"]').length > 0 || 
                                       $('meta[name="extension-installed"]').length > 0;
                
                var installedVer = $('meta[name="toolsbydcx-extension-version"]').attr('content') ||
                                   $('meta[name="wemate-extension-version"]').attr('content') || 
                                   $('meta[name="extension-version"]').attr('content') || 
                                   '1.0.0';

                if (!extInstalledMeta || isOutdated(installedVer, requiredVer)) {
                    var modal = $('#panelExtensionUpdateModal');
                    if (!modal.length) return;

                    if (isStrictForce) {
                        modal.modal('show');
                    } else {
                        var lastSnooze = localStorage.getItem('panel_update_snooze') || 0;
                        var now = new Date().getTime();
                        if (now - parseInt(lastSnooze) > SNOOZE_MS) {
                            modal.modal('show');
                        }
                    }

                    $('#panelUpdateSnoozeBtn').on('click', function() {
                        localStorage.setItem('panel_update_snooze', new Date().getTime());
                    });
                }
            }

            setTimeout(checkPanelExtensionUpdate, 1500);

            // Responsive sidebar toggle
            $('.res-sidebar-open-btn').on('click', function (){
                $('.sidebar').addClass('open');
            }); 
            $('.res-sidebar-close-btn').on('click', function (){
                $('.sidebar').removeClass('open');
            });

            $('.select2').select2();
        })(jQuery);
    </script>
    @endauth

    @stack('script')

</body>
</html>
