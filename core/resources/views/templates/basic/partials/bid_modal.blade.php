    {{-- BIT MODAL --}}
    <div class="modal fade custom--modal" id="bitModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-block mb-3">
                    <h4 class="product-details__right-title acc_title mb-1">@lang('Product Title')</h4>
                    <a class="product-details__right-link acc_link fs-14" href="" target="_blank"> </a>
                    <button class="btn-close modal-icon" data-bs-dismiss="modal" type="button" aria-label="Close"> <i
                            class="las la-times"></i></button>
                </div>
                <form action="{{ route('user.direct.payment') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <input name="account_listing_id" type="hidden">
                        <div class="remaining-time my-3 type-auction">
                            <div class="type-bid">
                                <div class="remaining_time mb-1"></div>
                                <div class="text-center">
                                    <p class="mt-0 remaining-time__desc fs-12">
                                        @lang('Last Time of Bid: ') <span class="end_time text--base"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="social-activity__list mb-3">
                            <h6 class="mb-1 text--base fs-16">@lang('Account Information')</h6>
                            <ul class="list-group list-group-flush"></ul>
                        </div>
                        <div class="type-fixed mb-3">
                            <h6 class="mb-1 text--base fs-16">@lang('Price')</h6>
                            <span class="fs-14 current-bid-style sell_price"></span>
                        </div>

                        <div class="type-auction mb-3">
                            <h6 class="mb-1 text--base fs-16">@lang('Current Bid')</h6>
                            <span class="fs-14 current-bid"></span>
                        </div>
                        <div class="type-auction mb-3">
                            <h6 class="mb-1 text--base fs-16">@lang('Instant Buy Price')</h6>
                            <span class="fs-14 instant-buy_price"></span>
                        </div>

                        <input type="hidden" name="payment_type" value="deposit">
                        <div class="mb-3">
                            <h6 class="mb-1 text--base fs-16">
                                @lang('Payment Via')
                            </h6>
                            <div class="payment-options-wrapper">
                                <div class="payment-options" data-payment-type="balance">
                                    <span class="active-badge"> <i class="las la-check"></i> </span>
                                    <img src="{{ getImage($activeTemplateTrue . '/images/wallet.png') }}" alt="@lang('Payment Option Image')">
                                    <div class="payment-options-content">
                                        <h4 class="mb-1">@lang('Wallet Balance')</h4>
                                        <p>@lang('Payment completed instantly with one click if sufficient balance is available')</p>
                                    </div>
                                </div>
                                <div class="payment-options active" data-payment-type="deposit">
                                    <span class="active-badge"> <i class="las la-check"></i> </span>
                                    <img src="{{ getImage($activeTemplateTrue . '/images/credit-card.png') }}" alt="@lang('Payment Option Image')">
                                    <div class="payment-options-content">
                                        <h4 class="mb-1">@lang('Payment Gateway')</h4>
                                        <p>@lang('Multiple gateways for ensuring a seamless &amp; hassle-free payment process.')</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 type-auction">
                            <h6 class="title add-update-amount text--base mb-1">@lang('Update Amount')</h6>
                            <div class="d-flex  gap-3 gap-md-2 flex-wrap">
                                <div class="flex-fill">
                                    <div class="qty input-group">
                                        <button type="button"
                                            class="qtyminus input-group-text  custom-input-group-text">−</button>
                                        <input id="qty" name="amount" type="number" step="1"
                                            class="form-control form--control update--amount">
                                        <button type="button" class="qtyplus input-group-text  custom-input-group-text">+</button>
                                    </div>
                                </div>
                                <button class="btn btn--base bid-now-btn flex-fill outline" name="submit_type"
                                    type="submit" value="bid"> @lang('Bid Now')
                                </button>
                                <button class="btn btn--base flex-fill" name="submit_type" type="submit"
                                    value="buy">
                                    <i class="fas fa-shopping-bag"></i>@lang('BUY NOW')
                                </button>
                            </div>
                        </div>
                        <div class="type-fixed">
                            <div class="product-details__button">
                                <button class="btn btn--base " name="submit_type" value="buy">
                                    <i class="fas fa-shopping-bag"></i>@lang('BUY NOW')
                                </button>
                            </div>
                        </div>
                        <div class="update-bid-amount">
                            <p class="product-details__current-bid d-none mb-0">
                                @lang('Your Bid Amount'): <span class="current-bid-style update--bid-amount"></span>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('script')
        <script>
            "use strict";
            (function($) {
                $('.payment-options').on('click', function(e) {
                    $(`input[name=payment_type]`).val($(this).data(`payment-type`));
                    $(this).addClass('active').siblings().removeClass('active');
                });
            })(jQuery);
        </script>
    @endpush
