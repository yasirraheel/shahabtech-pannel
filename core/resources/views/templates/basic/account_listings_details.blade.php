@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="py-60">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5 pe-lg-5">
                    <div class="product-details__wrapper">
                        <div class="product-details__item">
                            <img src="{{ getImage(getFilePath('account_listing_thumb') . '/' . $accountListing->thumbnail_image, getFileSize('account_listing_thumb')) }}">
                        </div>
                        @foreach ($accountListing->images as $image)
                            <div class="product-details__item">
                                <img src="{{ getImage(getFilePath('account_listing_images') . '/' . $image->name, getFileSize('account_listing_images')) }}">
                            </div>
                        @endforeach
                    </div>
                    <div class="product-details__gallery">
                        <div class="product-gallery__item">
                            <img src="{{ getImage(getFilePath('account_listing_thumb') . '/thumb_' . $accountListing->thumbnail_image, getFileSize('account_listing_thumb')) }}">
                        </div>
                        @foreach ($accountListing->images as $image)
                            <div class="product-gallery__item">
                                <img src="{{ getImage(getFilePath('account_listing_images') . '/thumb_' . $image->name, getFileSize('account_listing_images')) }}">
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-7 ps-lg-5">
                    <div class="product-details__right">
                        <div class="d-flex justify-content-between  gap-1">
                            <div>
                                <h4 class="product-details__right-title mb-0"> {{ __($accountListing->title) }} </h4>
                                <a class="product-details__right-link fs-14" href="{{ $accountListing->url }}"
                                    target="_blank">
                                    {{ $accountListing->url }}
                                </a>
                            </div>
                            @auth
                                <span class="listing-report list_report  text--danger" data-bs-toggle="tooltip"
                                    data-bs-html="true" title="@lang('Report')">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                            @endauth
                        </div>
                        @if ($accountListing->pricing_model == Status::AUCTION)
                            <div class="my-3 my-lg-5">
                                <div class="remaining-time">
                                    <div class="remaining-time__content d-flex remaining--time justify-content-center gap-4"
                                        data-date="{{ $accountListing->auctionDeadlineFormate }}">
                                        <p data-before="@lang('DAY')" class="box">
                                            <span class="box__days"></span>
                                        </p>
                                        <p data-before="@lang('HOURS')" class="box">
                                            <span class="remaining-time__hrs"></span>
                                        </p>
                                        <p data-before="@lang('MINUTE')" class="box">
                                            <span class="remaining-time__min"></span>
                                        </p>
                                        <p data-before="@lang('SECONDS')" class="box">
                                            <span class="remaining-time__sec"></span>
                                        </p>
                                    </div>
                                    <p class="mt-0 remaining-time__desc fs-14 text-center mt-2">
                                        @lang('Last Time of Bid: ') <span class="end_time text--base">
                                            {{ showDateTime($accountListing->auction_deadline, 'M d, Y h:ia') }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        @endif
                        <div class="mb-3 mt-3">
                            <h6 class="mb-0 fs-16">@lang('Account Information')</h6>
                            <ul class="list-group list-group-flush">
                                @if ($accountListing->account_info)
                                    @foreach ($accountListing->account_info as $val)
                                        @continue(!$val->value)
                                        <li
                                            class="list-group-item  d-flex align-items-center justify-content-between flex-wrap ps-1 fs-14">
                                            <span>{{ __($val->name) }}</span>
                                            <span>
                                                @if ($val->type == 'checkbox')
                                                    {{ implode(',', $val->value) }}
                                                @elseif($val->type == 'file')
                                                    <a class="me-3"
                                                        href="{{ route('user.attachment.download', encrypt(getFilePath('verify') . '/' . $val->value)) }}"><i
                                                            class="fa fa-file"></i> @lang('Attachment') </a>
                                                @else
                                                    <span>{{ __($val->value) }}</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        <div class="mb-3">
                            @if ($accountListing->pricing_model == Status::AUCTION)
                                <p class="product-details__current-bid">
                                <h6 class="mb-1 fs-16">@lang('Current Bid:')</h6>
                                @if (@$accountListing->account_bidding_max_amount)
                                    <span class="current-bid-style accouunt-bid-max fs-14">
                                        {{ showAmount($accountListing->account_bidding_max_amount) }}
                                    </span>
                                @else
                                    <span class="current-bid-style accouunt-bid-max fs-14">
                                        @lang('You will place the first bid for this account')
                                    </span>
                                @endif
                                </p>
                            @else
                                <h6 class="mb-1 fs-16">@lang('Fixed Price:')</h6>
                                <span
                                    class="current-bid-style fs-14">{{ showAmount($accountListing->sell_price) }}</span>
                            @endif
                        </div>
                        <form action="{{ route('user.direct.payment') }}" method="post">
                            @csrf
                            <input type="hidden" name="pricing_model" value="{{ $accountListing->pricing_model }}">
                            <input name="account_listing_id" type="hidden" value="{{ $accountListing->id }}">
                            <input type="hidden" name="payment_type" value="deposit">
                            <div class="mb-3">
                                <h6 class="mb-2 fs-16">
                                    @lang('Payment Via')
                                </h6>
                                <div class="payment-options-wrapper">
                                    <div class="payment-options" data-payment-type="balance">
                                        <span class="active-badge"> <i class="las la-check"></i> </span>
                                        <img src="{{ getImage($activeTemplateTrue . '/images/wallet.png') }}">
                                        <div class="payment-options-content">
                                            <h4 class="mb-1">@lang('Wallet Balance')</h4>
                                            <p>@lang('Payment completed instantly with one click if sufficient balance is available')</p>
                                        </div>
                                    </div>
                                    <div class="payment-options active" data-payment-type="deposit">
                                        <span class="active-badge"> <i class="las la-check"></i> </span>
                                        <img src="{{ getImage($activeTemplateTrue . '/images/credit-card.png') }}">
                                        <div class="payment-options-content">
                                            <h4 class="mb-1">@lang('Payment Gateway')</h4>
                                            <p>@lang('Multiple gateways for ensuring a seamless &amp; hassle-free payment process.')</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($accountListing->pricing_model == Status::AUCTION)
                                <h6 class="mb-1 fs-16 add-amount">
                                    @if (@$myBid)
                                        @lang('Update Amount:')
                                    @else
                                        @lang('Bid Amount:')
                                    @endif
                                </h6>
                                <div class="product-details__quantity d-flex gap-3">
                                    <div class="flex-fill">
                                        <div class="qty input-group">


                                            <button data-input="update-amount" type="button"
                                                class="qtyminus input-group-text custom-input-group-text">
                                                <i class="las la-minus"></i>
                                            </button>
                                            @if (@$myBid)
                                                @php
                                                    $maxBidAmount = $accountListing->sell_price - $myBid->amount;
                                                @endphp
                                                <input autocomplete="off" class="form-control form--control update--amount"
                                                    name="amount" type="number" value="1" min="1"
                                                    max="{{ $maxBidAmount }}" data-my-bid="{{ $myBid->amount }}"
                                                    step="any">
                                            @else
                                                <input class="form-control form--control update--amount" name="amount" type="number"
                                                    value="{{ getAmount($accountListing->min_price) }}"
                                                    min="{{ getAmount($accountListing->min_price) }}"
                                                    max="{{ $accountListing->sell_price }}" step="1">
                                            @endif

                                            <button data-input="update-amount" type="button"
                                                class="qtyplus input-group-text custom-input-group-text">
                                                <i class="las la-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button class="btn btn--base bid-now-btn flex-fill outline" name="submit_type"
                                        type="submit" value="bid"> <i class="las la-hand-spock"></i>
                                        @if (@$myBid)
                                            @lang('Update Bid')
                                        @else
                                            @lang('Bid Now')
                                        @endif
                                    </button>
                                    <button class="btn btn--base flex-fill" name="submit_type" type="submit"
                                        value="buy">
                                        <i class="fas fa-shopping-bag"></i>@lang('Buy Now')
                                    </button>
                                </div>
                                @if (@$myBid)
                                    <div class="current-bid-update">
                                        <p class="product-details__current-bid mb-0"> @lang('Your bid Amount:')
                                            <span
                                                class="current-bid-style update--bid-amount">{{ showAmount($myBid->amount) }}</span>
                                        </p>
                                    </div>
                                @endif
                            @else
                                <button class="btn btn--base" name="submit_type" type="submit" value="buy">
                                    @lang('Buy Now')
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="product-details__tab">
                        <ul class="nav nav-pills custom--tab tab-two" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-descrip-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-descrip" type="button" role="tab">
                                    @lang('Description')
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-history-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-history" type="button" role="tab">
                                    @lang('Auction History')
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-seller-tab" data-bs-toggle="pill"
                                    data-bs-target="#pills-seller" type="button" role="tab">
                                    @lang('Seller Information')
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-descrip" role="tabpanel"
                                aria-labelledby="pills-descrip-tab" tabindex="0">
                                <div class="product-description">
                                    @php echo $accountListing->description @endphp
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-history" role="tabpanel"
                                aria-labelledby="pills-history-tab" tabindex="0">
                                <div class="auction-history-table">
                                    <table class="table--responsive--lg table">
                                        <thead>
                                            <tr>
                                                <th> @lang('Date')</th>
                                                <th> @lang('Bid') </th>
                                                <th> @lang('User') </th>
                                                @if (@$myBid)
                                                    <th> @lang('Cancel') </th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($biddings as $bidding)
                                                <tr>
                                                    <td> {{ showDateTime($bidding->created_at, 'M d, Y h:ia') }}
                                                    </td>
                                                    <td> {{ showAmount($bidding->amount) }}
                                                    </td>
                                                    <td> {{ __(@$bidding->user->full_name) }} </td>
                                                    @if (@$myBid)
                                                        <td>
                                                            @if ($bidding->user->id == auth()->id())
                                                                <button class="badge badge--danger confirmationBtn"
                                                                    data-question="@lang('Are you sure to cancel this bid?')"
                                                                    data-action="{{ route('user.account.listing.cancel.bid', @$bidding->id) }}"
                                                                    data-bs-toggle="tooltip" data-bs-html="true"
                                                                    title="@lang('Cancel Bid')"> <i
                                                                        class="las la-trash"></i> </button>
                                                            @else
                                                                @lang('N/A')
                                                            @endif
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center" colspan="100%">
                                                        @include($activeTemplate . 'empty', [
                                                            'message' => $emptyMessage,
                                                        ])
                                                    </td>
                                                </tr>
                                            @endforelse

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-seller" role="tabpanel"
                                aria-labelledby="pills-seller-tab" tabindex="0">
                                <div class="seller-info-wrapper">
                                    <div class="row">
                                        <div class="col-lg-5">
                                            <div class="card custom--card">
                                                <div
                                                    class="card-body d-flex flex-column justify-content-center align-items-center">
                                                    <div class="seller-info__thumb text-center">
                                                        @if ($accountListing->user->image)
                                                            <img
                                                                src="{{ getImage(getFilePath('userProfile') . '/' . $accountListing->user->image, getFileSize('userProfile')) }}">
                                                        @else
                                                            <img
                                                                src="{{ getImage($activeTemplateTrue . '/images/avatar.png') }}">
                                                        @endif
                                                    </div>
                                                    <h5 class="seller-info__title my-3">
                                                        {{ __(@$accountListing->user->fullname) }}
                                                    </h5>
                                                    <div class="seller-info__content">
                                                        <ul class="list-group list-group-flush">
                                                            <li
                                                                class="list-group-item d-flex flex-wrap justify-content-between">
                                                                <span>@lang('City'):</span>
                                                                <span>{{ __(@$accountListing->user->city) }}</span>
                                                            </li>
                                                            <li
                                                                class="list-group-item d-flex flex-wrap justify-content-between">
                                                                <span>@lang('State'):</span>
                                                                <span>{{ __(@$accountListing->user->state) }}</span>
                                                            </li>
                                                            <li
                                                                class="list-group-item d-flex flex-wrap justify-content-between">
                                                                <span>@lang('Country'): </span>
                                                                <span>{{ __(@$accountListing->user->country_name) }}</span>
                                                            </li>
                                                        </ul>
                                                        <ul class="social-list justify-content-center mt-3">
                                                            @if (@$accountListing->user->userSocialMedia->facebook)
                                                                <li class="social-list__item">
                                                                    <a class="social-list__link flex-center"
                                                                        href="{{ @$accountListing->user->userSocialMedia->facebook }}"
                                                                        target="_blank">
                                                                        <i class="fab fa-facebook-f"></i>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if (@$accountListing->user->userSocialMedia->twitter)
                                                                <li class="social-list__item"><a
                                                                        class="social-list__link flex-center"
                                                                        href="{{ @$accountListing->user->userSocialMedia->twitter }}"
                                                                        target="_blank">
                                                                        <i class="fab fa-twitter"></i>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if (@$accountListing->user->userSocialMedia->linkedin)
                                                                <li class="social-list__item"><a
                                                                        class="social-list__link flex-center"
                                                                        href="{{ @$accountListing->user->userSocialMedia->linkedin }}"
                                                                        target="_blank">
                                                                        <i class="fab fa-linkedin-in"></i>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if (@$accountListing->user->userSocialMedia->instagram)
                                                                <li class="social-list__item"><a
                                                                        class="social-list__link flex-center"
                                                                        href="{{ @$accountListing->user->userSocialMedia->linkedin }}"
                                                                        target="_blank">
                                                                        <i class="fab fa-instagram"></i>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if (@$accountListing->user->userSocialMedia->youtube)
                                                                <li class="social-list__item">
                                                                    <a class="social-list__link flex-center"
                                                                        href="{{ @$accountListing->user->userSocialMedia->youtube }}"
                                                                        target="_blank">
                                                                        <i class="fab fa-youtube"></i>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="related-product">
                        <h4 class="related-product__title"> @lang('Related Accounts') </h4>
                        @forelse ($relatedAccounts as $accountListing)
                            @include($activeTemplate . 'partials.product_item')
                        @empty
                            @include($activeTemplate . 'empty', [
                                'message' => 'There is no related account list',
                            ])
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade custom--modal" id="reportModal" role="dialog" aria-labelledby="reportModalLabel"
        aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-block">
                    <h4 class="product-details__right-title acc_title mb-0"> @lang('Report') </h4>
                    <button class="btn-close modal-icon" data-bs-dismiss="modal" type="button" aria-label="Close"> <i
                            class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="account-form">
                        <form class="verify-gcaptcha" method="POST" action="{{ route('user.report') }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12 form-group">
                                    <div class="form--group">
                                        <label class="form--label">@lang('Report massage')</label>
                                        <textarea class="form--control" name="report"></textarea>
                                        <input name="listing_id" type="hidden" value="{{ $accountListing->id }}">
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <button class="btn btn--base w-100" type="submit"> @lang('Submit') </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include($activeTemplate . 'partials.login_modal')
    @include($activeTemplate . 'partials.bid_modal')
    @include($activeTemplate . 'partials.buy_button_and_count_js')
    @php
        $addClass = 'custom--modal';
    @endphp
    <x-confirmation-modal :addClass="$addClass" :customButton=true />
@endsection

@if (!app()->offsetExists('slick_style'))
    @push('style-lib')
        <link href="{{ asset($activeTemplateTrue . 'css/slick.css') }}" rel="stylesheet">
    @endpush
    @php app()->offsetSet('slick_style',true) @endphp
@endif

@if (!app()->offsetExists('slick_script'))
    @push('script-lib')
        <script src="{{ asset($activeTemplateTrue . 'js/slick.min.js') }}"></script>
    @endpush
    @php app()->offsetSet('slick_script',true) @endphp
@endif


@push('script')
    <script>
        (function($) {
            "use strict";
            $('.product-details__wrapper').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                dots: false,
                fade: true,
                asNavFor: '.product-details__gallery',
                prevArrow: '<button type="button" class="slick-prev gig-details-thumb-arrow"><i class="las la-long-arrow-alt-left"></i></button>',
                nextArrow: '<button type="button" class="slick-next gig-details-thumb-arrow"><i class="las la-long-arrow-alt-right"></i></button>',
            });

            $('.product-details__gallery').slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                asNavFor: '.product-details__wrapper',
                dots: false,
                arrows: true,
                infinite: false,
                focusOnSelect: true,
                prevArrow: '<button type="button" class="slick-prev gig-details-arrow"><i class="fas fa-chevron-left"></i></button>',
                nextArrow: '<button type="button" class="slick-next gig-details-arrow"><i class="fas fa-chevron-right"></i></button>',
                responsive: [{
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 676,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 460,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    },
                ]
            });

            if ("{{ auth()->check() }}") {

                $('.list_report').on('click', function() {
                    var reportModal = $('#reportModal');
                    reportModal.modal('show');
                    return true;
                });
            }


        })(jQuery);
    </script>
@endpush


@push('style')
    <style>
        .remaining-time .box {
            position: relative;
        }

        .remaining-time .box span {
            color: hsl(var(--white)/0.7);
        }
    </style>
@endpush
