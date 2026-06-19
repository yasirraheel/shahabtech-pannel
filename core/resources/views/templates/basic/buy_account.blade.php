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

            <div class="row gy-4 mt-3">
                @forelse ($platforms as $platform)
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="product-item">
                            <div class="product-item__wrapper">
                                <div class="product-item__thumb d-flex justify-content-center pt-4 pb-2">
                                    <div class="icon-wrap" style="width: 80px; height: 80px; background: rgba(108, 99, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="las la-globe" style="font-size: 3.5rem; color: var(--base-color, #6c63ff);"></i>
                                    </div>
                                </div>
                                <div class="product-item__content text-center">
                                    <h4 class="product-item__title mb-1">
                                        <span class="text--base">{{ __($platform->name) }}</span>
                                    </h4>
                                    <p class="product-item__text mb-2" style="font-family: monospace; color: #dc3545;">
                                        {{ $platform->domain }}
                                    </p>
                                    <div class="mt-2 mb-2">
                                        <span class="badge badge--success px-3 py-2" style="font-size: 12px; background: transparent; border: 1px solid #28a745; color: #28a745;">
                                            {{ $platform->account_listing_count }} @lang('Accounts Available')
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-wrap justify-content-center">
                                <div class="product-item__button w-100 mt-3">
                                    <a href="{{ $platform->url }}" target="_blank" class="btn btn--base w-100">
                                        <i class="las la-external-link-square-alt me-1"></i> @lang('Visit Platform')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <h5 class="text-muted">@lang('No platforms currently available.')</h5>
                    </div>
                @endforelse
            </div>
            
            @if ($platforms->hasPages())
                <div class="row mt-5">
                    <div class="col-12">
                        {{ paginateLinks($platforms) }}
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if (@$sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif

@endsection
