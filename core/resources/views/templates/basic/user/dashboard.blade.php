@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-section py-120">
        <div class="container">
            <div class="notice"></div>
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
                                {{ $user->plan ? __($user->plan->name) : 'No Active Plan' }}
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
            
            <div class="dashboard-body">
                <div class="row gy-4">
                    <div class="col-xl-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">@lang('My Accessible Platforms')</h4>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                @forelse ($platforms as $platform)
                                    <div class="product-item">
                                        <div class="product-item__wrapper">
                                            <div class="product-item__thumb">
                                                <div style="width: 80px; height: 80px; background: rgba(108, 99, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                    <i class="las la-globe" style="font-size: 3.5rem; color: var(--base-color, #6c63ff);"></i>
                                                </div>
                                            </div>
                                            <div class="product-item__content">
                                                <h4 class="product-item__title d-flex align-items-center mb-0">
                                                    <span class="text--base">{{ __($platform->name) }}</span>
                                                </h4>
                                                <p class="product-item__text mt-2" style="font-family: monospace; color: #dc3545;">
                                                    {{ $platform->domain }}
                                                </p>
                                                <div class="mt-2">
                                                    <span class="badge badge--success" style="background: transparent; border: 1px solid #28a745; color: #28a745; font-size: 13px; padding: 6px 12px;">
                                                        {{ $platform->account_listing_count }} @lang('Accounts Available')
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <div class="product-item__button">
                                                <button type="button" class="btn btn--base btn-inject-access" data-platform-id="{{ $platform->id }}">
                                                    <i class="las la-external-link-square-alt me-1"></i> <span class="btn-text">@lang('Visit Platform')</span>
                                                </button>
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
            $('.btn-inject-access').on('click', function(e) {
                e.preventDefault();
                let btn = $(this);
                let btnText = btn.find('.btn-text');
                let originalText = btnText.text();
                let platformId = btn.data('platform-id');
                
                // Check if extension is installed by looking for the meta tag injected by content.js
                if ($('meta[name="shahabtech-extension-installed"]').length === 0) {
                    notify('error', 'ShahabTech Access Extension is not installed or enabled.');
                    return;
                }

                btn.prop('disabled', true);
                btnText.text('Loading...');

                $.ajax({
                    url: '{{ url("api/extension/cookies") }}/' + platformId,
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
