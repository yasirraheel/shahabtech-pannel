@php
    $countdown = @$countdown ?? true;
@endphp

@push('script')
    <script>
        "use strict";
        (function($) {

            $('.bitBtn').on('click', function() {

                @guest
                    $('#loginModal').modal('show');
                    return;
                @endguest

                var modal              = $('#bitModal');
                var accountListing     = $(this).data('account-listing');
                var amount             = $(this).data('amount');
                var maxBid             = $(this).data('max_bid');
                var auctionEndAtFormat = $(this).data('auction_end_at_format');
                var sellPrice          = $(this).data('sell_price');
                var maxPrice           = $(this).data('max_price');
                var myBidCount         = $(this).data('my-bid-count');
                var myBidAmount        = $(this).data('my-bid-amount');
                var curSym             = `{{ gs('cur_sym') }}`;
                let socialActivity     = "";

                $(accountListing.account_info || []).each(function(index, item) {
                    socialActivity += `<li class="list-group-item  d-flex align-items-center justify-content-between flex-wrap ps-1 fs-14">
                        <span>${item.name}</span>
                        <span>${item.value}</span>
                    </li>`
                });

                if (accountListing.pricing_model == 2) {
                    modal.find('input[name=amount]').val(amount);
                    modal.find('input[name=amount]').attr("readonly", true);
                    modal.find('.add-update-amount').addClass('d-none');

                    modal.find(`.type-auction`).addClass(`d-none`);
                    modal.find(`.type-fixed`).removeClass(`d-none`);

                } else {
                    modal.find('input[name=amount]').val('');
                    modal.find('input[name=amount]').attr("readonly", false);
                    modal.find('.add-update-amount').removeClass('d-none');

                    modal.find(`.type-auction`).removeClass(`d-none`);
                    modal.find(`.type-fixed`).addClass(`d-none`);
                }

                    modal.find('input[name=account_listing_id]').val(accountListing.id);
                    modal.find('.acc_title').html(accountListing.title);
                    modal.find('.acc_link').html(accountListing.url);
                    modal.find('.acc_link').attr("href", accountListing.url);
                    modal.find('.social-activity__list ul').html(socialActivity);
                    

                if (maxBid <= 0) {
                    modal.find('.current-bid').html("@lang('You will place the first bid for this account.')");
                    modal.find('input[name=amount]').val(amount);
                    modal.find('.bid-now-btn').html(`<i class="las la-hand-spock"></i>@lang('BID NOW')`)
                    modal.find('.update-bid-amount p').addClass('d-none');
                    modal.find('.qtyplus').removeClass('count-active').attr('max-bid', '');
                    modal.find('.qtyminus').removeClass('count-active').attr('max-bid', '');
                    modal.find('.add-update-amount').text('Bid Amount:');
                } else {
                    
                    modal.find('.current-bid').text(`${maxBid}`);
                    if (myBidCount) {
                        modal.find('.bid-now-btn').html(`<i class="las la-hand-spock"></i>@lang('UPDATE BID')`);
                        modal.find('input[name=amount]').attr('data-my-bid',myBidAmount);
                        modal.find('.update-bid-amount p').removeClass('d-none');
                        modal.find('.update-bid-amount p span').html(curSym+parseFloat(myBidAmount).toFixed(2));
                        modal.find('.add-update-amount').text('Update Amount:');
                    } else {
                        modal.find('.bid-now-btn').html(`<i class="las la-hand-spock"></i>@lang('BID NOW')`)
                        modal.find('input[name=amount]').val(parseFloat(maxBid.replace(/[^0-9.-]+/g, "")) + 1);
                        modal.find('.add-update-amount').html(`@lang('Bid Amount:')`);
                        modal.find('.update-bid-amount p').addClass('d-none');
                    }
                }

                modal.find('.sell_price').html(sellPrice);
                modal.find('.instant-buy_price').html(sellPrice);

                if (myBidCount) {
                    modal.find('input[name=amount]').attr("min", 1);
                    modal.find('input[name=amount]').attr("max", Number(sellPrice) - Number(myBidAmount));
                    modal.find('input[name=amount]').val(1);
                } else {
                    modal.find('input[name=amount]').attr("max", maxPrice);
                    modal.find('input[name=amount]').attr("min", amount);
                    modal.find('input[name=amount]').val(amount);
                }

                let date          = $(this).data('date');
                let remainingTime = `<div class="remaining-time__content d-flex remaining--time-modal" data-date="${date}">
                    <p data-before = "@lang('DAYS')" class    = "box box-two"><span class = "box__days"></span></p>
                    <p data-before = "@lang('HOURS')" class   = "box box-two"><span class = "remaining-time__hrs"></span></p>
                    <p data-before = "@lang('MINUTES')" class = "box box-two"><span class = "remaining-time__min"></span></p>
                    <p data-before = "@lang('SECONDS')" class = "box box-two"><span class = "remaining-time__sec"></span></p>
                </div>`;

                modal.find('.remaining_time').html(remainingTime);
                modal.find('.end_time').html(auctionEndAtFormat);

                remainIngTime('.remaining--time-modal');
                modal.modal('show');
            });
          
            $(document).on('click', '.qtyplus, .qtyminus', function() {
                calulateBid($(this));
            });

            $(document).on('input', '.update--amount', function() {
                calulateBid($(this));
            });

            function calulateBid($this){

                var inputElement = $this.closest(`.input-group`).find('.update--amount');
                var curretValue  = Number(inputElement.val());

                if(curretValue  <= 1 && $this.hasClass('qtyminus')){
                    curretValue=1;
                    inputElement.val(1);
                    return;
                }
                
                var maxBid       = Number(inputElement.attr('max'));
                var minBid       = Number(inputElement.attr('min'));
                var myBid        = Number(inputElement.attr('data-my-bid') || 0);
                var curSym       = `{{ gs('cur_sym') }}`;

                if((curretValue >= maxBid) && !$this.hasClass('qtyminus')){
                    return;
                };

                if((curretValue <= minBid) && !$this.hasClass('qtyplus')){
                    return;
                };

                if($this.hasClass('qtyplus')){
                    curretValue++;
                }

                if($this.hasClass('qtyminus')){
                    curretValue--;
                }

                inputElement.val(curretValue);

                if(!myBid) return;

                
                if($this.closest('.modal').length > 0){
                    $('#bitModal').find('.update--bid-amount').text(curSym+parseFloat(myBid+curretValue).toFixed(2));
                    
                }else{
                    $('.product-details__right').find('.update--bid-amount').text(curSym+parseFloat(myBid+curretValue).toFixed(2));
                }
            }

            function remainIngTime(selector = ".remaining--time") {
                $($(selector)).each(function(index, element) {
                    let duration = $(element).data('date');
                    if (duration) {
                        const targetDate = new Date(duration).getTime();
                        if (!targetDate) return;
                        setInterval(function() {
                            const currentDate = new Date().getTime();
                            const remainingTime = targetDate - currentDate;

                            if (remainingTime <= 0) return;

                            const days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 *
                                60 * 60));
                            const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 *
                                60));
                            const seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

                            $(element).find('.box__days').html(`${days}`);
                            $(element).find('.remaining-time__hrs').html(`${ hours < 10 ? `0${hours}`:hours}`);
                            $(element).find('.remaining-time__min').html(`${ minutes < 10 ? `0${minutes}`:minutes}`);
                            $(element).find('.remaining-time__sec').html(`${ seconds < 10 ? `0${seconds}`:seconds}`);
                        }, 1000);
                    }
                });
            }
            @if($countdown)
                remainIngTime();
            @endif

        })(jQuery);
    </script>
@endpush
