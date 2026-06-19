<div class="product-item">
    <div class="product-item__wrapper">
        <div class="product-item__thumb">
            <a class="product-item__thumb"
                href="{{ route('account.listing.details', [slug($accountListing->title), $accountListing->id]) }}">
                <img src="{{ getImage(getFilePath('account_listing_thumb') . '/' . $accountListing->thumbnail_image, getFileSize('account_listing_thumb')) }}"
                    alt="image">
            </a>
        </div>
        <div class="product-item__content">
            <h4 class="product-item__title d-flex align-items-center mb-0">
                <a class="text--base" href="{{ route('account.listing.details', [slug($accountListing->title), $accountListing->id]) }}">{{ __(strLimit($accountListing->title, 20)) }}</a>
                @if ($accountListing->is_verified == Status::VERIFIED)
                    <span class="product-item__badge ms-1" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Verified">
                        <span class="product-item__badge-icon"><i class="las la-check"></i></span>
                    </span>
                @endif
            </h4>
            @if ($accountListing->pricing_model == Status::AUCTION)
                <p class="product-item__text">
                    @lang('Minimum Bid:')
                    {{ showAmount($accountListing->min_price) }}
                </p>
                <p class="product-item__text">
                    @lang('Current Bid:')
                    {{ showAmount($accountListing->account_bidding_max_amount) }}
                </p>
            @else
                <p class="product-item__text"> 
                  {{ showAmount($accountListing->sell_price) }}
                </p>
            @endif
        </div>
    </div>
    <div class="d-flex  align-items-center flex-wrap">
        @if ($accountListing->pricing_model == Status::AUCTION)
            <div class="remaining-time me-4">
                <div class="remaining-time__content remaining--time"
                    data-date="{{ $accountListing->auctionDeadlineFormate }}">
                    <p class="box box-two"><span class="box__days"></span></p>
                    <p class="box box-two"> <span class="remaining-time__hrs"></p>
                    <p class="box box-two"> <span class="remaining-time__min"></p>
                    <p class="box box-two"> <span class="remaining-time__sec"></p>
                </div>
            </div>
        @endif
        <div class="product-item__button">
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

                @if ($accountListing->pricing_model == Status::AUCTION)
                    @if ($accountListing->my_bid_count)
                        @lang('Update Bid')
                    @else
                        @lang('Bid Now')
                    @endif
                @else
                    @lang('Buy Now')
                @endif
            </button>
        </div>
    </div>
</div>
