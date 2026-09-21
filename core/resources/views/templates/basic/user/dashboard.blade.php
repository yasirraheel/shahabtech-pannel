@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="dashboard-section">
        {{-- Answered Ticket Alert Banner --}}
        @php
            $userAnsweredTickets = \App\Models\SupportTicket::where('user_id', auth()->id())->where('status', \App\Constants\Status::TICKET_ANSWER)->get();
            $totalUserTickets = \App\Models\SupportTicket::where('user_id', auth()->id())->count();
        @endphp
        @if($userAnsweredTickets->isNotEmpty())
            <div class="mb-4">
                @foreach($userAnsweredTickets as $ansTicket)
                    <div class="alert alert--info d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2" style="background-color: rgba(13, 110, 253, 0.15); border: 1px solid #0d6efd; color: #ffffff; padding: 14px 20px; border-radius: 8px;">
                        <div class="d-flex align-items-center me-3">
                            <i class="las la-envelope-open-text me-3" style="font-size: 28px; color: #0d6efd;"></i>
                            <div>
                                <h6 class="mb-1" style="color: #60a5fa; font-weight: 700;">@lang('Support Team Answered Your Ticket!')</h6>
                                <div style="font-size: 14px; color: #e2e8f0;">@lang('Ticket') <strong>#{{ $ansTicket->ticket }}</strong>: {{ strLimit($ansTicket->subject, 60) }}</div>
                            </div>
                        </div>
                        <a href="{{ route('ticket.view', $ansTicket->ticket) }}" class="btn btn-sm btn--primary text-nowrap" style="padding: 8px 16px; font-size: 13px; font-weight: 600;">
                            <i class="las la-eye me-1"></i> @lang('View Reply')
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Subscription Expiry Calculation --}}
        @php
            $expiryDate = auth()->user()->expires_at ?: auth()->user()->created_at->addDays(30);
            $isExpired = auth()->user()->expires_at ? now()->greaterThanOrEqualTo($expiryDate) : false;
            if (!auth()->user()->expires_at && !auth()->user()->is_trial) {
                $isExpired = now()->greaterThanOrEqualTo($expiryDate);
            }
            if (auth()->user()->is_trial && auth()->user()->pending_trial_minutes > 0) {
                $isExpired = false;
            }

            $diffInDays = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($expiryDate)->startOfDay(), false);
            $diffInHours = now()->diffInHours(\Carbon\Carbon::parse($expiryDate), false);
            
            if ($isExpired) {
                $validityText = 'Expired';
            } elseif (auth()->user()->is_trial && auth()->user()->pending_trial_minutes > 0) {
                $validityText = 'Trial Pending';
            } elseif ($diffInDays > 1) {
                $validityText = $diffInDays . ' Days Left';
            } elseif ($diffInDays == 1) {
                $validityText = '1 Day Left';
            } elseif ($diffInHours > 0) {
                $validityText = $diffInHours . ' Hours Left';
            } else {
                $validityText = 'Expires Today';
            }
        @endphp

        @if($isExpired)
            <div class="alert alert-danger mb-4 shadow-sm" role="alert" style="font-size: 1rem; padding: 16px 20px; border-left: 5px solid #dc3545; background-color: rgba(220, 53, 69, 0.15); color: #ff8585; border-radius: 8px;">
                <i class="las la-exclamation-triangle me-2" style="font-size: 1.4rem; vertical-align: middle;"></i>
                <strong>@lang('Subscription Expired'):</strong> @lang('Your access validity has expired. Please contact support or purchase a plan to renew your tools.')
            </div>
        @endif

        {{-- App Native Dashboard Items (Focused on Tools & Validity) --}}
        <div class="row gy-4 mb-4">
            <div class="col-lg-3 col-sm-6">
                <div class="dashboard-item">
                    <div class="dashboard-item__content">
                        <span class="dashboard-item__title"> @lang('Current Plan') </span>
                        <h3 class="dashboard-item__currency" style="color: var(--base-color);">
                            @if(auth()->user()->is_trial)
                                @if(auth()->user()->pending_trial_minutes > 0)
                                    @lang('Trial Pending')
                                @else
                                    @lang('Trial Active')
                                @endif
                            @else
                                {{ $user->plan ? __($user->plan->name) : 'Standard Access' }}
                            @endif
                        </h3>
                    </div>
                    <span class="dashboard-item__icon"> <i class="fas fa-crown"></i> </span>
                </div>
            </div>
            
            <div class="col-lg-3 col-sm-6">
                <div class="dashboard-item">
                    <div class="dashboard-item__content">
                        <span class="dashboard-item__title"> @lang('Validity Remaining') </span>
                        <h3 class="dashboard-item__currency" style="color: {{ $isExpired ? '#dc3545' : 'var(--base-color)' }};">
                            {{ $validityText }}
                        </h3>
                    </div>
                    <span class="dashboard-item__icon"> <i class="las la-hourglass-half"></i> </span>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="dashboard-item">
                    <div class="dashboard-item__content">
                        <span class="dashboard-item__title"> @lang('Assigned Tools') </span>
                        <h3 class="dashboard-item__currency">
                            {{ count((array)($user->account_ids ?? [])) }} @lang('Available')
                        </h3>
                    </div>
                    <span class="dashboard-item__icon"> <i class="las la-cubes"></i> </span>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="dashboard-item">
                    <div class="dashboard-item__content">
                        <a class="dashboard-item__title" href="{{ route('ticket.index') }}"> @lang('Support Tickets') </a>
                        <h3 class="dashboard-item__currency">
                            {{ $totalUserTickets }} @lang('Tickets')
                        </h3>
                    </div>
                    <span class="dashboard-item__icon"> <i class="las la-headset"></i> </span>
                </div>
            </div>
        </div>

        {{-- Accessible Tools & Platforms Section (App's native product-item style) --}}
        <div class="row">
            <div class="col-12">
                <div class="card custom--card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">@lang('Accessible Tools & Platforms')</h4>
                        <span class="badge bg--primary px-3 py-2">
                            <i class="las la-check-circle me-1"></i> {{ count((array)($user->account_ids ?? [])) }} @lang('Tools Unlocked')
                        </span>
                    </div>
                    <div class="card-body">
                        @if(@$isAdmin && !empty($adminAccounts) && $adminAccounts->isNotEmpty())
                            <div class="mb-4 p-3 d-flex align-items-center gap-3" style="background: rgba(234, 179, 8, 0.12); border: 1px solid rgba(234, 179, 8, 0.35); border-left: 4px solid #eab308; border-radius: 8px;">
                                <div style="width: 34px; height: 34px; border-radius: 6px; background: rgba(234, 179, 8, 0.2); display: flex; align-items: center; justify-content: center; color: #facc15; flex-shrink: 0;">
                                    <i class="las la-vial" style="font-size: 20px;"></i>
                                </div>
                                <div style="font-size: 13.5px; line-height: 1.4;">
                                    <strong class="text-white">@lang('Tester Mode Active'):</strong> <span style="color: #fef08a;">@lang('Showing all active accounts with full developer controls.')</span>
                                </div>
                            </div>
                            @foreach ($adminAccounts as $acc)
                                @php
                                    $platformObj = $acc->socialMedia;
                                    $instructions = $platformObj->instructions ?: $acc->instructions;
                                @endphp
                                <div class="product-item">
                                    <div class="product-item__wrapper">
                                        <div class="product-item__thumb">
                                            <div style="width: 70px; height: 70px; background: rgba(108, 99, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                <i class="las la-globe" style="font-size: 3rem; color: var(--base-color, #6c63ff);"></i>
                                            </div>
                                        </div>
                                        <div class="product-item__content">
                                            <h4 class="product-item__title d-flex align-items-center mb-0">
                                                <span class="text--base">{{ __($platformObj->name) }} ({{ __($acc->title) }})</span>
                                            </h4>
                                            @if($instructions)
                                                <div class="mt-2" style="font-size: 0.85rem; line-height: 1.4; color: #b3b3b3; max-width: 85%;">
                                                    <strong class="d-block mb-1" style="color: var(--base-color, #6c63ff);"><i class="las la-info-circle"></i> @lang('Instructions')</strong>
                                                    {{ $instructions }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap flex-shrink-0">
                                        <div class="product-item__button d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn--base btn-inject-access d-inline-flex align-items-center justify-content-center text-nowrap" data-platform-id="{{ $acc->social_media_id }}" data-account-id="{{ $acc->id }}">
                                                <i class="las la-external-link-square-alt me-1"></i> <span class="btn-text">@lang('Visit Platform')</span>
                                            </button>
                                            <button type="button" class="btn btn--info btn-copy-cookie d-inline-flex align-items-center justify-content-center text-nowrap" data-platform-id="{{ $acc->social_media_id }}" data-account-id="{{ $acc->id }}">
                                                <i class="las la-copy me-1"></i> <span class="btn-copy-text">@lang('Copy Cookie')</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @php
                                $platformCounters = [];
                                $isExclusive = (bool) auth()->user()->is_exclusive || (bool) @$isAdmin;
                            @endphp
                            @forelse ($assignedAccounts as $acc)
                                @php
                                    $platform = $acc->socialMedia;
                                    $pId = $acc->social_media_id;
                                    $instructions = $platform->instructions ?: $acc->instructions;
                                    $platformAccCount = $assignedAccounts->where('social_media_id', $pId)->count();
                                    
                                    $platformCounters[$pId] = ($platformCounters[$pId] ?? 0) + 1;
                                    $accIndexNumber = $platformCounters[$pId];
                                @endphp
                                <div class="product-item">
                                    <div class="product-item__wrapper">
                                        <div class="product-item__thumb">
                                            <div style="width: 70px; height: 70px; background: rgba(108, 99, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                <i class="las la-globe" style="font-size: 3rem; color: var(--base-color, #6c63ff);"></i>
                                            </div>
                                        </div>
                                        <div class="product-item__content">
                                            <h4 class="product-item__title d-flex align-items-center mb-0">
                                                @if($platformAccCount > 1)
                                                    <span class="text--base">{{ __($platform->name) }} #{{ $accIndexNumber }}</span>
                                                @else
                                                    <span class="text--base">{{ __($platform->name) }}</span>
                                                @endif
                                            </h4>
                                            @if($instructions)
                                                <div class="mt-2" style="font-size: 0.85rem; line-height: 1.4; color: #b3b3b3; max-width: 85%;">
                                                    <strong class="d-block mb-1" style="color: var(--base-color, #6c63ff);"><i class="las la-info-circle"></i> @lang('Instructions')</strong>
                                                    {{ $instructions }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap flex-shrink-0">
                                        <div class="product-item__button d-flex align-items-center gap-2">
                                            @if($isExpired)
                                                <button type="button" class="btn btn--secondary text-nowrap" disabled style="opacity: 0.6; cursor: not-allowed;">
                                                    <i class="las la-ban me-1"></i> <span class="btn-text">@lang('Expired')</span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn--base btn-inject-access d-inline-flex align-items-center justify-content-center text-nowrap" data-platform-id="{{ $acc->social_media_id }}" data-account-id="{{ $acc->id }}">
                                                    <i class="las la-external-link-square-alt me-1"></i> <span class="btn-text">@lang('Visit Platform')</span>
                                                </button>
                                                @if($isExclusive)
                                                    <button type="button" class="btn btn--info btn-copy-cookie d-inline-flex align-items-center justify-content-center text-nowrap" data-platform-id="{{ $acc->social_media_id }}" data-account-id="{{ $acc->id }}">
                                                        <i class="las la-copy me-1"></i> <span class="btn-copy-text">@lang('Copy Cookie')</span>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <div class="card custom--card border-0">
                                        <div class="card-body py-5">
                                            <i class="las la-folder-open mb-3" style="font-size: 3rem; color: #888;"></i>
                                            <h5 class="text-muted">@lang('You currently do not have access to any platforms.')</h5>
                                            <p class="text-muted">@lang('Please purchase a plan to unlock premium platforms.')</p>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    (function($){
        "use strict";

        function fallbackCopyText(text) {
            let textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {}
            document.body.removeChild(textArea);
        }

        $('.btn-copy-cookie').on('click', function(e) {
            e.preventDefault();
            let btn = $(this);
            let btnText = btn.find('.btn-copy-text');
            let originalText = btnText.text();
            let platformId = btn.data('platform-id');
            let accountId = btn.data('account-id');

            btn.prop('disabled', true);
            btnText.text('Fetching...');

            let ajaxUrl = '{{ url("api/extension/cookies") }}/' + platformId;
            if (accountId) {
                ajaxUrl += '/' + accountId;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                success: function(response) {
                    if (response.success && response.cookies) {
                        let jsonCookies = JSON.stringify(response.cookies, null, 2);
                        
                        function copySuccess() {
                            notify('success', 'Cookie copied to clipboard in JSON format!');
                            btnText.text('Copied!');
                            setTimeout(function() {
                                btn.prop('disabled', false);
                                btnText.text(originalText);
                            }, 2500);
                        }

                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(jsonCookies).then(function() {
                                copySuccess();
                            }).catch(function() {
                                fallbackCopyText(jsonCookies);
                                copySuccess();
                            });
                        } else {
                            fallbackCopyText(jsonCookies);
                            copySuccess();
                        }
                    } else {
                        notify('error', response.message || 'Failed to fetch cookie data.');
                        btn.prop('disabled', false);
                        btnText.text(originalText);
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to fetch cookie data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    notify('error', msg);
                    btn.prop('disabled', false);
                    btnText.text(originalText);
                }
            });
        });

        $('.btn-inject-access').on('click', function(e) {
            e.preventDefault();
            let btn = $(this);
            let btnText = btn.find('.btn-text');
            let originalText = btnText.text();
            let platformId = btn.data('platform-id');
            let accountId = btn.data('account-id');
            
            // Check if extension is installed by looking for the meta tag injected by content.js
            if ($('meta[name="toolsbydcx-extension-installed"]').length === 0 &&
                $('meta[name="wemate-extension-installed"]').length === 0 &&
                $('meta[name="shahabtech-extension-installed"]').length === 0 && 
                $('meta[name="extension-installed"]').length === 0) {
                notify('error', '{{ __(gs("site_name")) }} Access Extension is not installed or enabled.');
                return;
            }

            btn.prop('disabled', true);
            btnText.text('Loading...');

            let ajaxUrl = '{{ url("api/extension/cookies") }}/' + platformId;
            if (accountId) {
                ajaxUrl += '/' + accountId;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        btnText.text('Injecting...');
                        
                        // Send custom event to extension's content.js
                        let event1 = new CustomEvent('ShahabTechInject', {
                            detail: {
                                platform: response.platform,
                                cookies: response.cookies
                            }
                        });
                        window.dispatchEvent(event1);

                        let event2 = new CustomEvent('ToolsByDcxInject', {
                            detail: {
                                platform: response.platform,
                                cookies: response.cookies
                            }
                        });
                        window.dispatchEvent(event2);
                        
                        setTimeout(function() {
                            btn.prop('disabled', false);
                            btnText.text('Opened');
                            setTimeout(() => btnText.text(originalText), 3000);
                        }, 1500);
                    } else {
                        notify('error', response.message || 'Failed to fetch access credentials.');
                        btn.prop('disabled', false);
                        btnText.text(originalText);
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to process request.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    notify('error', msg);
                    btn.prop('disabled', false);
                    btnText.text(originalText);
                }
            });
        });
    })(jQuery);
</script>
@endpush
