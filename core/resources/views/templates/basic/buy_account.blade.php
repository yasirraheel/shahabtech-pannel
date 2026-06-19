@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="auction-section py-60">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="swiper mySwiper">
                        <ul class="nav nav-pills custom--tab tab-three swiper-wrapper" id="pills-tabtwo" role="tablist">
                            <li class="nav-item swiper-slide" role="presentation">
                                <a class="nav-link {{ request('social_media_id') == '' ? 'active' : '' }}"
                                    href="{{ appendQuery('social_media_id', null) }}">
                                    @lang('All')
                                </a>
                            </li>
                            @foreach ($socialsMedia as $socialMedia)
                                <li class="nav-item swiper-slide" role="presentation">
                                    <a class="nav-link {{ request('social_media_id') == $socialMedia->id ? 'active' : '' }}"
                                        href="{{ appendQuery('social_media_id', $socialMedia->id) }}">
                                        {{ __($socialMedia->name) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a class="swiper-button-next"></a>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-3">
                    <form>
                        <div class="left-sidebar">
                            <span class="close-sidebar d-xl-none d-block">
                                <i class="las la-times"></i>
                            </span>
                            <div class="sidebar-item">
                                <div class="form-group">
                                    <input class="form--control" name="search" type="text" type="search" value="{{ request('search') }}" placeholder="@lang('Search')">
                                </div>
                            </div>
                            <div class="sidebar-item">
                                <div class="sidebar-item__top d-flex justify-content-between">
                                    <h6 class="sidebar-item__title">
                                        @lang('Categories')
                                    </h6>
                                </div>
                                @foreach ($categories as $category)
                                    @php
                                        if(request()->category_id){
                                            if(is_array(request()->category_id)){
                                                $categoriesId=request()->category_id;
                                            }else{
                                                $categoriesId=(array) request()->category_id;
                                            }
                                        }else{
                                            $categoriesId=[];
                                        }

                                    @endphp
                                    <div class="form-check check-two form--check">
                                        <input class="form-check-input" id="{{ $category->id }}" name="category_id[]"
                                            type="checkbox" value="{{ $category->id }}" @checked(in_array($category->id,$categoriesId))>
                                        <label class="form-check-label" for="{{ $category->id }}">
                                            {{ __($category->name) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="sidebar-item">
                                <div class="sidebar-item__top d-flex justify-content-between">
                                    <h6 class="sidebar-item__title">
                                        @lang('Pricing Type')
                                    </h6>
                                </div>
                                <div class="social-media__check payment__check">
                                    <div class="form--check pb-3">
                                        <input class="form-check-input" id="all" name="pricing_model" type="radio" value="" @checked(request('pricing_model') == '')>
                                        <label class="form-check-label" for="all">
                                            @lang('All') 
                                        </label>
                                    </div>
                                    <div class="form--check pb-3">
                                        <input class="form-check-input" id="auction" name="pricing_model" type="radio"
                                            value="1" @checked(request('pricing_model') == 1)>
                                        <label class="form-check-label" for="auction"> @lang('Auction') </label>
                                    </div>
                                    <div class="form--check">
                                        <input class="form-check-input" id="fixed_price" name="pricing_model" type="radio" value="2" @checked(request('pricing_model') == 2)>
                                        <label class="form-check-label" for="fixed_price">
                                            @lang('Fixed Price') 
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="sidebar-item">
                                <div class="sidebar-item__top d-flex justify-content-between">
                                    <h6 class="sidebar-item__title">
                                        @lang('Price Range')
                                    </h6>
                                </div>
                                <div class="custom--range">
                                    <div class="custom--range__range ui-slider ui-corner-all ui-slider-horizontal ui-widget ui-widget-content" id="slider-range">
                                        <div class="ui-slider-range ui-corner-all ui-widget-header"></div><span
                                            class="ui-slider-handle ui-corner-all ui-state-default"
                                            tabindex="0"></span><span
                                            class="ui-slider-handle ui-corner-all ui-state-default" tabindex="0"></span>
                                    </div>
                                    <div class="custom--range__content">
                                        <input class="custom--range__prices" id="amount" name="amount" type="text"
                                            readonly="">
                                    </div>
                                </div>
                            </div>
                            <div class="sidebar-item">
                                <button class="btn btn--base w-100" type="submit">@lang('Filter')</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xl-9 ps-lg-5">
                    <div class="sidebar-filter d-xl-none d-block">
                        <button class="sidebar-filter__button">
                            <i class="las la-filter"></i>
                            <span class="text"> @lang('Filter') </span>
                        </button>
                    </div>
                    <div>
                        @forelse ($allSocialsMedia as $accountListing)
                            @include($activeTemplate . 'partials.product_item')
                        @empty
                            @include($activeTemplate . 'empty', [
                                'message' => 'No account listings currently exist.',
                            ])
                        @endforelse
                        @if ($allSocialsMedia->hasPages())
                            {{ paginateLinks($allSocialsMedia) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include($activeTemplate . 'partials.login_modal')
    @include($activeTemplate . 'partials.bid_modal')
    @include($activeTemplate . 'partials.buy_button_and_count_js')

    @if (@$sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif

@endsection

@push('style-lib')
    <link href="{{ asset($activeTemplateTrue . 'css/swiper.css') }}" rel="stylesheet">
    <link href="{{ asset($activeTemplateTrue . 'css/range.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/swiper.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/range.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

           


            var swiper = new Swiper(".mySwiper", {
                slidesPerView: 4,
                spaceBetween: 0,
                pagination: {
                    el: ".swiper-pagination",
                    type: "fraction",
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    575: {
                        slidesPerView: 8,
                        spaceBetween: 0,
                    },
                    992: {
                        slidesPerView: 10,
                        spaceBetween: 0,
                    },
                },
            });



            let currency = `{{ gs('cur_sym') }}`;

            $("#slider-range").slider({
                range: true,
                min: {{ $minPrice }},
                max: {{ $maxPrice }},
                values: [{{ $min }}, {{ $max }}],
                slide: function(event, ui) {
                    $("#amount").val(currency + ui.values[0] + " - " + currency + ui.values[1]);
                }
            });
            $("#amount").val(currency + $("#slider-range").slider("values", 0) + "-" + currency + $("#slider-range")
                .slider("values", 1));
        })(jQuery);
    </script>
@endpush
