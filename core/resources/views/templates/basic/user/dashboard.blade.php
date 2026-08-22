@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-section py-120">
        <div class="container">
            <div class="notice"></div>
            @php
                $userAnsweredTickets = \App\Models\SupportTicket::where('user_id', auth()->id())->where('status', \App\Constants\Status::TICKET_ANSWER)->get();
            @endphp
            @if($userAnsweredTickets->isNotEmpty())
                <div class="mb-4">
                    @foreach($userAnsweredTickets as $ansTicket)
                        <div class="alert alert--info d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2" style="background-color: rgba(13, 110, 253, 0.15); border: 1px solid #0d6efd; color: #ffffff; padding: 14px 20px; border-radius: 8px;">
                            <div class="d-flex align-items-center me-3">
                                <i class="las la-envelope-open-text me-3" style="font-size: 28px; color: #0d6efd;"></i>
                                <div>
                                    <h6 class="mb-1" style="color: #60a5fa; font-weight: 700;">@lang('Admin Answered Your Ticket!')</h6>
                                    <div style="font-size: 14px; color: #e2e8f0;">@lang('Ticket') <strong>#{{ $ansTicket->ticket }}</strong>: {{ strLimit($ansTicket->subject, 60) }}</div>
                                </div>
                            </div>
                            <a href="{{ route('ticket.view', $ansTicket->ticket) }}" class="btn btn-sm btn--primary text-nowrap" style="padding: 8px 16px; font-size: 13px; font-weight: 600; background-color: #0d6efd; border-color: #0d6efd;">
                                <i class="las la-eye me-1"></i> @lang('View Reply')
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="row justify-content-center">
                <div class="col-md-12">
                    @php
                        $kyc = getContent('kyc.content', true);
                    @endphp
                    @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
                        <div class="card custom--card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h4 class="alert-heading">@lang('KYC Documents Rejected')</h4>
                                    <button class="btn btn--base btn-sm" data-bs-toggle="modal" data-bs-target="#kycRejectionReason">@lang('Show Reason')</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>{{ __(@$kyc->data_values->reject) }} <a href="{{ route('user.kyc.form') }}">@lang('Click Here to Re-submit Documents')</a>.</p>
                                <br>
                                <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a>
                            </div>
                        </div>
                    @elseif(auth()->user()->kv == Status::KYC_UNVERIFIED)
                        <div class="card custom--card mb-4">
                            <div class="card-header">
                                <h5 class="alert-heading m-0">@lang('KYC Verification required')</h5>
                            </div>
                            <div class="card-body">
                                <p>{{ __(@$kyc->data_values->required) }} <a href="{{ route('user.kyc.form') }}">@lang('Click Here to Submit Documents')</a></p>
                            </div>

                        </div>
                    @elseif(auth()->user()->kv == Status::KYC_PENDING)
                        <div class="card custom--card mb-4">
                            <div class="card-header">
                                <h4 class="alert-heading">@lang('KYC Verification pending')</h4>
                            </div>
                            <div class="card-body">
                                <p>{{ __(@$kyc->data_values->pending) }} <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
                <div class="modal custom--modal fade" id="kycRejectionReason">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                                <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>{{ auth()->user()->kyc_rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row gy-4 mb-5">
                <div class="col-lg-4 col-sm-6">
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
                                    {{ $user->plan ? __($user->plan->name) : 'No Active Plan' }}
                                @endif
                            </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="fas fa-crown"></i> </span>
                    </div>
                </div>
                
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-item">
                        <div class="dashboard-item__content">
                            <a class="dashboard-item__title" href="{{ route('user.transactions') }}"> @lang('Current Balance') </a>
                            <h3 class="dashboard-item__currency"> {{ showAmount($user->balance) }} </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="fas fa-wallet"></i> </span>
                    </div>
                </div>
                
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-item">
                        <div class="dashboard-item__content">
                            <a class="dashboard-item__title" href="{{ route('user.deposit.history') }}"> @lang('Total Deposit') </a>
                            <h3 class="dashboard-item__currency"> {{ showAmount($totalDeposit) }} </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="menu-icon las la-file-invoice-dollar"></i> </span>
                    </div>
                </div>
            </div>
            
            @php
                $expiryDate = auth()->user()->expires_at ?: auth()->user()->created_at->addDays(30);
                $isExpired = auth()->user()->expires_at ? now()->greaterThanOrEqualTo($expiryDate) : false;
                if (!auth()->user()->expires_at && !auth()->user()->is_trial) {
                    $isExpired = now()->greaterThanOrEqualTo($expiryDate);
                }
                if (auth()->user()->is_trial && auth()->user()->pending_trial_minutes > 0) {
                    $isExpired = false;
                }
            @endphp

            @if($isExpired)
                <div class="alert alert-danger mb-5" role="alert" style="font-size: 1.1rem; padding: 20px; border-left: 5px solid #dc3545; background-color: #fff3f3; color: #dc3545;">
                    <i class="las la-exclamation-triangle" style="font-size: 1.5rem; vertical-align: middle;"></i> <strong>@lang('Your subscription is expired.')</strong> @lang('If you want to keep using please contact administrator.')
                </div>
            @endif

            <div class="dashboard-body">
                <div class="row gy-4">
                    <div class="col-xl-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">@lang('My Accessible Platforms')</h4>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                @if(@$isAdmin && !empty($adminAccounts) && $adminAccounts->isNotEmpty())
                                    <div class="mb-3 text-warning">
                                        <small><i class="las la-vial"></i> <strong>Tester Mode:</strong> Showing all active accounts with their original titles for testing.</small>
                                    </div>
                                    @foreach ($adminAccounts as $acc)
                                        @php
                                            $platformObj = $acc->socialMedia;
                                            $instructions = $platformObj->instructions ?: $acc->instructions;
                                        @endphp
                                        <div class="product-item">
                                            <div class="product-item__wrapper">
                                                <div class="product-item__thumb">
                                                    <div style="width: 80px; height: 80px; background: rgba(108, 99, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                        <i class="las la-globe" style="font-size: 3.5rem; color: var(--base-color, #6c63ff);"></i>
                                                    </div>
                                                </div>
                                                <div class="product-item__content">
                                                    <h4 class="product-item__title d-flex align-items-center mb-0">
                                                        <span class="text--base">{{ __($platformObj->name) }} <small class="text-white ms-2">({{ __($acc->title) }})</small></span>
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
                                                    <div style="width: 80px; height: 80px; background: rgba(108, 99, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                        <i class="las la-globe" style="font-size: 3.5rem; color: var(--base-color, #6c63ff);"></i>
                                                    </div>
                                                </div>
                                                <div class="product-item__content">
                                                    <h4 class="product-item__title d-flex align-items-center mb-0">
                                                        @if($platformAccCount > 1)
                                                            <span class="text--base">{{ __($platform->name) }} {{ $accIndexNumber }}</span>
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
                                            <div class="card custom--card">
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
        </div>
    </div>
    <x-confirmation-modal addClass="custom--modal" :customButton=true />
    
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
                if ($('meta[name="shahabtech-extension-installed"]').length === 0 && $('meta[name="extension-installed"]').length === 0) {
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
                            let event = new CustomEvent('ShahabTechInject', {
                                detail: {
                                    platform: response.platform,
                                    cookies: response.cookies
                                }
                            });
                            window.dispatchEvent(event);
                            
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
@endsection
