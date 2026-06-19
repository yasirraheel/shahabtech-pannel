@php
    $topSellingContent = getContent('top_account.content', true);
    $accountListings = App\Models\AccountListing::searchable(['title'])
        ->with(['category'])
        ->active()
        ->activeSocialMedia()
        ->activeCategory()
        ->withCount('accountBidding')
        ->withMax('accountBidding', 'amount')
        ->orderBy('auction_deadline', 'asc')
        ->where('pricing_model', Status::AUCTION)
        ->myBidCount()
        ->MyBid()
        ->checkPreviousDate()
        ->limit(10)
        ->get();
@endphp

@if (!blank($accountListings))
    <div class="influential-profile-section py-120 section-bg-two">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="section-heading">
                        <span class="section-heading__subtitle"> {{ __(@$topSellingContent->data_values->title) }}
                        </span>
                        <h3 class="section-heading__title"> {{ __(@$topSellingContent->data_values->heading) }} </h3>
                    </div>
                </div>
            </div>
            <div class="table-responsive account__tab pt-3">
                <table class="table--responsive--lg table">
                    <thead>
                        <tr>
                            <th> @lang('Account Name') </th>
                            <th> @lang('Category') </th>
                            <th> @lang('Pricing Model') </th>
                            <th> @lang('Bids') </th>
                            <th> @lang('Minimum Price') </th>
                            <th> @lang('Time Left') </th>
                            <th> @lang('Place Bid') </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accountListings as $accountListing)
                            <tr>
                                <td>
                                    <div class="account">
                                        <p class="account__name d-flex">
                                            <a
                                                href="{{ route('account.listing.details', [slug($accountListing->title), $accountListing->id]) }}">
                                                {{ __($accountListing->title) }}
                                            </a>
                                            @if ($accountListing->is_verified == Status::VERIFIED)
                                                <span class="product-item__badge ms-1" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="@lang('Verified')"> <span
                                                        class="product-item__badge-icon"><i
                                                            class="las la-check"></i></span></span>
                                            @endif
                                        </p>
                                    </div>
                                </td>
                                <td> {{ __($accountListing->category->name) }} </td>
                                <td>
                                    <span
                                        class="badge {{ $accountListing->pricing_model == Status::AUCTION ? 'badge--base' : 'badge--warning' }}">
                                        {{ __($accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed') }}
                                    </span>
                                </td>
                                <td> {{ @$accountListing->account_bidding_count }} </td>
                                <td>

                                    {{ showAmount($accountListing->pricing_model == Status::AUCTION ? $accountListing->min_price : $accountListing->sell_price) }}

                                </td>
                                <td>
                                    <span class="time">
                                        @if ($accountListing->pricing_model == Status::AUCTION)
                                            {{ dayHours($accountListing->auction_deadline) }}
                                        @else
                                            @lang('Unlimited')
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn--base bitBtn"
                                        data-my-bid-amount={{ getAmount(@$accountListing->accountBidding[0]->amount) }}
                                        data-my-bid-count={{ $accountListing->my_bid_count }}
                                        data-max_price="{{ getAmount($accountListing->sell_price) }}"
                                        data-account-listing='@json($accountListing)'
                                        data-sell_price="{{ showAmount($accountListing->sell_price) }}"
                                        data-max_bid="{{ showAmount($accountListing->account_bidding_max_amount) }}"
                                        data-auction_end_at_format="{{ $accountListing->pricing_model == Status::AUCTION ? showDateTime($accountListing->auction_deadline, 'M d, Y h:ia') : 'N/A' }}"
                                        data-amount={{ getAmount($accountListing->pricing_model == Status::AUCTION ? $accountListing->min_price : $accountListing->sell_price) }}
                                        data-date="{{ $accountListing->auctionDeadlineFormate }}">
                                        @if ($accountListing->my_bid_count)
                                            @lang('Update Bid')
                                        @else
                                            @lang('Bid Now')
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include($activeTemplate . 'partials.login_modal')
    @include($activeTemplate . 'partials.bid_modal')
    @include($activeTemplate . 'partials.buy_button_and_count_js',['countdown' => false])
 
@endif
