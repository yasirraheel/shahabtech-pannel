@extends($activeTemplate . 'layouts.master')
@section('content')
    @php
        $accDesctiptionContent = getContent('sell_account_form.content', true);
        if (@$accountListing->status == Status::LISTING_ACTIVE || @$accountListing->status == Status::LISTING_INACTIVE) {
            $listStatus = 'd-none';
        }
    @endphp
    <div class="social-media-section py-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading">
                        <h2 class="section-heading__title mb-3"> @lang('Bidding Information')</h2>
                        <p class="section-heading__desc m-auto">
                            {{ @$accDesctiptionContent->data_values->bidding_information }} </p>
                    </div>
                </div>
            </div>
            <div class="row gx-0 justify-content-center">
                <div class="col-xl-3 col-md-5 {{ @$listStatus }}">
                    @include(activeTemplate() . 'partials.progress_bar')
                </div>
                <div class="col-xl-9 col-md-7">
                    <div class="social-media__body">
                        <form id="biddingInfoForm">
                            @if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE)
                                <div class="form-group">
                                    <label class="form--label">@lang('Title')</label>
                                    <input class="form-control form--control" name="title" type="text" value="{{ old('title', $accountListing->title) }}">
                                </div>
                            @endif
                            <div class="form-group">
                                <div class="social-media__pricing">
                                    <label class="form--label"> @lang('Pricing Model') </label>
                                </div>
                                <div class="social-media__check payment__check d-flex">
                                    <div class="form--check">
                                        <input class="form-check-input" id="auction" name="pricing_model" type="radio" value="1" checked @checked($accountListing->pricing_model == Status::AUCTION)>
                                        <label class="form-check-label" for="auction">
                                            @lang('Auction')
                                        </label>
                                    </div>
                                    <div class="form--check">
                                        <input class="form-check-input" id="fixed-price" name="pricing_model" type="radio" value="2" @checked($accountListing->pricing_model == Status::FIXED)>
                                        <label class="form-check-label" for="fixed-price">
                                            @lang('Fixed Price')
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div
                                 class="form-group forAuction {{ @$accountListing->pricing_model == Status::FIXED ? 'd-none' : 'd-block' }}">
                                <label class="form--label" for="price">@lang('Minimum Bid Price')</label>
                                <div class="input-group">
                                    <input class="form-control form--control" name="min_price" type="number" value="{{ getAmount($accountListing->min_price) }}" step="any" required>
                                    <span class="input-group-text input--group--text">{{ __(gs('cur_text')) }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form--label" for="buy"> @lang('Price') </label>
                                <div class="input-group">
                                    <input class="form--control form-control" name="sell_price" type="number" value="{{ getAmount($accountListing->sell_price) }}" step="any" required>
                                    <span class="input-group-text input--group--text">{{ __(gs('cur_text')) }}</span>
                                </div>
                            </div>
                            <div class="form-group forAuction {{ @$accountListing->pricing_model == Status::FIXED ? 'd-none' : 'd-block' }}">
                                <label class="form--label" for="auction"> @lang('Auction Deadline') </label>
                                <input class="form--control" name="auction_deadline" type="text" value="" autocomplete="off" required>
                            </div>

                            <button class="btn btn--base" type="submit">
                                @if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE)
                                    @lang('Update')
                                @else
                                    @if ($accountListing->step == 1)
                                        @lang('Save & Continue')
                                    @else
                                        @lang('Save')
                                    @endif
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-lib')
    <script src="{{ asset('assets/global/js/nicEdit.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpush

@push('style-lib')
    <link type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}" rel="stylesheet" />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $('input[name=pricing_model]').on('click', function() {
                var priceType = Number($(this).val());

                if (priceType == 1) {
                    $('.forAuction').removeClass('d-none');
                    $('input[name=sell_price]').val('');

                    $('input[name=min_price]').attr(`required`, true);
                    $('input[name=auction_deadline]').attr(`required`, true);

                } else {
                    $('.forAuction').addClass('d-none');

                    $('input[name=min_price]').val('');
                    $('input[name=auction_deadline]').val('');

                    $('input[name=min_price]').removeAttr(`required`, true);
                    $('input[name=auction_deadline]').removeAttr(`required`, true);


                }
            });

            //Ajax
            $('#biddingInfoForm').on('submit', function(e) {
                e.preventDefault();

                var btn = $(this).find(`button[type=submit]`);
                var prevText = btn.text();
                var btnAfterSubmit = `<div class="spinner-border"></div> @lang('Saving')...`;

                btn.html(btnAfterSubmit);
                btn.attr('disabled', true);

                //store
                var formData = new FormData($('#biddingInfoForm')[0]);
                var url = '{{ route('user.account.listing.bidding.info.store', @$accountListing->id) }}';
                var token = '{{ csrf_token() }}';

                formData.append('_token', token);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 'success') {
                            if (!response.is_update) {
                                window.location.href = response.redirect_url
                            } else {
                                notify('success', "@lang('Bidding info updated successfully')");
                                btn.removeAttr('disabled');
                            }
                        } else {
                            notify('error', response.message);
                            btn.removeAttr('disabled');
                        }

                        btn.text(prevText);
                    },
                    error: function(xhr, status, error) {
                        notify('error', error);
                        btn.removeAttr('disabled');
                        btn.text(prevText);
                    }
                });
            });

            var nowDate = `{{ showDateTime(@$accountListing->auction_deadline == '' ? now() : $accountListing->auction_deadline, 'Y-m-d') }}`;
            console.log(nowDate);
            $('input[name="auction_deadline"]').daterangepicker({
                "singleDatePicker": true,
                "startDate": nowDate,
                "opens": "right",
                "locale": {
                    "format": "YYYY-MM-DD",
                }
            });


        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .daterangepicker {
            border: 1px solid hsl(var(--white) / .3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
            background-color: hsl(var(--black)) !important;
            color: hsl(var(--white)) !important;
        }

        .daterangepicker .drp-buttons {
            border-top: 1px solid hsl(var(--white) / .3);
        }

        .daterangepicker .calendar-table {
            border-radius: 4px;
            background-color: hsl(var(--black));
        }

        th.prev.available,
        th.next.available {
            background: hsl(var(--white) / .1);
            color: hsl(var(--white)) !important;
        }

        .daterangepicker .calendar-table .next span,
        .daterangepicker .calendar-table .prev span {
            border: solid hsl(var(--white));
            border-width: 0 2px 2px 0 !important;
        }

        .daterangepicker td.available:hover,
        .daterangepicker th.available:hover {
            background-color: hsl(var(--white) / .2) !important;
            color: hsl(var(--white));
        }

        .prev.available:hover span,
        .next.available:hover span {
            border: 2px solid hsl(var(--white)) !important;
            border-width: 0 2px 2px 0 !important;
        }

        .daterangepicker td.off,
        .daterangepicker td.off.in-range,
        .daterangepicker td.off.start-date,
        .daterangepicker td.off.end-date {
            background-color: transparent;
            color: hsl(var(--warning));
        }

        .daterangepicker td.active,
        .daterangepicker td.active:hover,
        .daterangepicker .ranges li.active {
            background-color: hsl(var(--base)) !important;
            color: hsl(var(--black));
        }
        
        .btn.applyBtn,
        .btn.cancelBtn  {
            height: 28px;
            font-size: 10px !important;
            font-weight: 500 !important;
        }

        .applyBtn.btn{
            background-color: hsl(var(--base)) !important;
            color: hsl(var(--black));
        }

        .cancelBtn.btn{
            background-color: hsl(var(--warning));
            color: hsl(var(--black));
        }

        .cancelBtn.btn:hover,
        .cancelBtn.btn:focus{
            border-color: hsl(var(--warning)) !important;
            background-color: hsl(var(--warning)) !important;
        }

        .applyBtn.btn:hover,
        .applyBtn.btn:focus{
            border-color: hsl(var(--base)) !important;
            color: hsl(var(--black));
        }

        .daterangepicker:before {
            top: -7px;
            border-right: 7px solid transparent;
            border-left: 7px solid transparent;
            border-bottom: 7px solid #ccc;
            height: 12px;
            width: 12px;
            border: 1px solid hsl(var(--white) / .3);
            border-width: 0 0 1px 1px;
            transform: rotate(-225deg);
        }

        .daterangepicker::after{
            display: none;
        }
        
    </style>
@endpush
