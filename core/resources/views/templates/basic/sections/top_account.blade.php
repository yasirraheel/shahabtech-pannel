@php
    $topSellingContent = getContent('top_account.content', true);
    $platforms = App\Models\SocialMedia::active()
        ->withCount(['accountListing' => function($q) {
            $q->where('status', Status::LISTING_ACTIVE);
        }])
        ->having('account_listing_count', '>', 0)
        ->limit(10)
        ->get();
@endphp

@if (!blank($platforms))
    <div class="influential-profile-section py-120 section-bg-two">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="section-heading">
                        <span class="section-heading__subtitle"> {{ __(@$topSellingContent->data_values->title) }}</span>
                        <h3 class="section-heading__title"> {{ __(@$topSellingContent->data_values->heading) }} </h3>
                    </div>
                </div>
            </div>
            <div class="row gy-4 mt-3">
                @foreach ($platforms as $platform)
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="product-item">
                            <div class="product-item__wrapper d-flex flex-column align-items-center justify-content-center py-5">
                                <div class="product-item__thumb mb-4" style="width: auto; height: auto;">
                                    <div class="icon-wrap" style="width: 80px; height: 80px; background: rgba(108, 99, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="las la-globe" style="font-size: 3.5rem; color: var(--base-color, #6c63ff);"></i>
                                    </div>
                                </div>
                                
                                <div class="product-item__content text-center w-100">
                                    <h4 class="product-item__title mb-2">
                                        <span class="text--base">{{ __($platform->name) }}</span>
                                    </h4>
                                    
                                    <p class="product-item__text mb-4" style="font-family: monospace; color: #dc3545;">
                                        {{ $platform->domain }}
                                    </p>
                                    
                                    <div class="mb-4">
                                        <span class="badge badge--success px-3 py-2" style="font-size: 14px; background: transparent; border: 1px solid #28a745; color: #28a745;">
                                            {{ $platform->account_listing_count }} @lang('Accounts Available')
                                        </span>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <a href="{{ $platform->url }}" target="_blank" class="btn btn--base w-100" style="padding: 12px 0;">
                                            <i class="las la-external-link-square-alt me-1"></i> @lang('Visit Platform')
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
