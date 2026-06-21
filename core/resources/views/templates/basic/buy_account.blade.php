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
                                    @auth
                                        @php
                                            $userHasAccess = auth()->user()->plan_id || (!empty(auth()->user()->account_ids) && \App\Models\AccountListing::whereIn('id', auth()->user()->account_ids)->where('social_media_id', $platform->id)->exists());
                                        @endphp
                                        @if($userHasAccess)
                                            <button type="button" class="btn btn--base btn-inject-access" data-platform-id="{{ $platform->id }}">
                                                <i class="las la-external-link-square-alt me-1"></i> <span class="btn-text">@lang('Visit Platform')</span>
                                            </button>
                                        @else
                                            <a href="{{ route('plans') }}" class="btn btn--base">
                                                <i class="las la-lock me-1"></i> @lang('Subscribe to Access')
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
