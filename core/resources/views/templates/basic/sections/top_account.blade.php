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
            <div class="row g-4 mt-3">
                @foreach ($platforms as $platform)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card text-center p-4 h-100">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="las la-globe" style="font-size: 3rem; color: var(--base-color, #6c63ff);"></i>
                                </div>
                                <h5 class="card-title">{{ __($platform->name) }}</h5>
                                <p class="text-muted small mb-3">
                                    <code>{{ $platform->domain }}</code>
                                </p>
                                <span class="badge badge--success mb-3">
                                    {{ $platform->account_listing_count }} @lang('Accounts Available')
                                </span>
                                <div>
                                    <a href="{{ $platform->url }}" target="_blank" class="btn btn--base btn-sm">
                                        <i class="las la-external-link-alt"></i> @lang('Visit')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
