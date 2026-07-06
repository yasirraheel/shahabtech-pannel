@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="auction-section py-60">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="section-heading text-center">
                        <span class="section-heading__subtitle"> @lang('Standout Accounts')</span>
                        <h3 class="section-heading__title"> @lang('Connect With Standout Social Media Influencers') </h3>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-10 mx-auto">
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
                                    @auth
                                        @php
                                            $account = null;
                                            $userHasAccess = auth()->user()->plan_id || (!empty(auth()->user()->account_ids) && \App\Models\AccountListing::whereIn('id', auth()->user()->account_ids)->where('social_media_id', $platform->id)->exists());
                                            if ($userHasAccess) {
                                                if (auth()->user()->plan_id) {
                                                    $account = $platform->accountListing()->where('plan_id', auth()->user()->plan_id)->where('status', \App\Constants\Status::LISTING_ACTIVE)->first();
                                                } elseif (!empty(auth()->user()->account_ids)) {
                                                    $account = $platform->accountListing()->whereIn('id', auth()->user()->account_ids)->where('status', \App\Constants\Status::LISTING_ACTIVE)->first();
                                                }
                                            }
                                        @endphp
                                        @if($account && $account->instructions)
                                            <div class="mt-2" style="font-size: 0.85rem; line-height: 1.4; color: #b3b3b3; max-width: 85%;">
                                                <strong class="d-block mb-1" style="color: var(--base-color, #6c63ff);"><i class="las la-info-circle"></i> @lang('Instructions')</strong>
                                                {{ $account->instructions }}
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-wrap flex-shrink-0">
                                <div class="product-item__button">
                                    @auth
                                        @php
                                            $userHasAccess = auth()->user()->plan_id || (!empty(auth()->user()->account_ids) && \App\Models\AccountListing::whereIn('id', auth()->user()->account_ids)->where('social_media_id', $platform->id)->exists());
                                        @endphp
                                        @if($userHasAccess)
                                            <button type="button" class="btn btn--base btn-inject-access d-inline-flex align-items-center justify-content-center text-nowrap" data-platform-id="{{ $platform->id }}">
                                                <i class="las la-external-link-square-alt me-1"></i> <span class="btn-text">@lang('Visit Platform')</span>
                                            </button>
                                        @else
                                            @php
                                                $contactContent = getContent('contact.content', true)->data_values;
                                                $whatsappNumber = preg_replace('/[^0-9]/', '', @$contactContent->phone_number);
                                                $whatsappMsg = urlencode("Hello, I am interested in getting access to the " . $platform->name . " platform. Please provide subscription details.");
                                                $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$whatsappMsg}";
                                            @endphp
                                            <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn--base text-nowrap" style="background-color: #25D366; border-color: #25D366; color: white;">
                                                <i class="lab la-whatsapp me-1" style="font-size: 1.2rem;"></i> @lang('Subscribe to Access')
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('user.login') }}" class="btn btn--base">
                                            <i class="las la-sign-in-alt me-1"></i> @lang('Login to Access')
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <h5 class="text-muted">@lang('No platforms currently available.')</h5>
                        </div>
                    @endforelse

                    @if ($platforms->hasPages())
                        <div class="mt-5">
                            {{ paginateLinks($platforms) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if (@$sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif

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
                
                // Check if extension is installed
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
