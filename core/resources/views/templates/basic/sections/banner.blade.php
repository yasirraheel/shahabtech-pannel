@php
$bannerContent = getContent('banner.content', true);
@endphp

<section class="banner-section">
<span class="banner-section__shape"></span>
<span class="banner-section__shape-two"></span>
<span class="banner-section__shape-three"></span>
<span class="banner-section__shape-four"></span>
<span class="banner-section__shape-five"></span>
<span class="banner-section__shape-six"></span>
<span class="banner-section__shape-seven"></span>
<span class="banner-section__shape-eight"></span>
<span class="banner-section__shape-nine"></span>
<span class="banner-section__shape-ten"></span>
<span class="banner-section__shape-eleven"></span>
<span class="banner-section__shape-twelve"></span>
<span class="banner-section__shape-thirteen"></span>
<span class="banner-section__shape-fourteen"></span>
<div class="container">
    <div class="row align-items-center justify-content-center gy-4">
        <div class="col-lg-7 col-md-7 pe-lg-5">
            <div class="banner-content">
                <span class="banner-content__badge"> {{ __(@$bannerContent->data_values->title) }}</span>
                <h1 class="banner-content__title"> {{ __(@$bannerContent->data_values->heading) }} </h1>
                <p class="banner-content__desc"> {{ __(@$bannerContent->data_values->subheading) }} </p>
                <div class="banner-content__button">
                    @guest
                        <form class="form-group" action="{{ route('user.register') }}">
                            <input class="form-control form--control" name="email" type="email" placeholder="@lang('Enter Your Email')">
                            <button class="btn btn--base banner-btn" type="submit"> {{ __(@$bannerContent->data_values->button_text)}}</button>
                        </form>
                    @endguest
                    <a class="banner-content__video" href="{{ @$bannerContent->data_values->video_link }}">
                        <span class="banner-content__icon"> <i class="las la-play"></i> </span>
                        <span class="text"> {{ __(@$bannerContent->data_values->video_button_text) }} </span>
                    </a>
                </div>
                <div class="banner-content__rating d-flex">
                    <div class="rating-thumb">
                        <img src="{{ frontendImage('banner', @$bannerContent->data_values->user_image,'135x40') }}" alt="Banner">
                        <span class="rating-thumb__number"> {{ @$bannerContent->data_values->total_user }} </span>
                    </div>
                    @php

                        @$fullRating = intVal(@$bannerContent->data_values->rating);

                        @$input = floatval(@$bannerContent->data_values->rating);
                        $helfRating = false;
                        @$helfForFullRating = false;
                        if (0 < ($input - floor(@$input)) * 10) {
                            if (6 > ($input - floor(@$input)) * 10) {
                                $helfRating = true;
                            } else {
                                @$helfForFullRating = true;
                            }
                        }

                        if($fullRating < 5){
                            $emptyRating = 5 - $fullRating;
                            if($helfRating || $helfForFullRating){
                                $emptyRating = $emptyRating - 1;
                            }
                        }
                    @endphp

                    <ul class="rating-list">

                        @for ($i = 1; $i <= @$fullRating; $i++)
                            <li class="rating-list__item"><i class="fas fa-star"></i></li>
                        @endfor

                        @if (@$helfForFullRating)
                        <li class="rating-list__item"><i class="fas fa-star"></i></li>
                        @endif

                        @if ($helfRating)
                            <li class="rating-list__item"><i class="fas fa-star-half-alt"></i></li>
                        @endif

                        @for ($i = 1; $i <= @$emptyRating; $i++)
                            <li class="rating-list__item"><i class="far fa-star"></i></li>
                        @endfor
                        <li class="rating-list__text ps-2"> {{ @$input }} </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-5 col-md-5 d-none d-md-block">
            <div class="banner-thumb">
                <img src="{{ frontendImage('banner', @$bannerContent->data_values->image, '526x465') }}" alt="@lang('banner')">
            </div>
        </div>
    </div>
</div>
</section>


@push('style-lib')
<link href="{{ asset('assets/global/css/magnific-popup.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
<script src="{{ asset('assets/global/js/magnific-popup.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {
                $('.banner-content__video').magnificPopup({
                    type: 'iframe'
                });
            });
        })(jQuery);
    </script>
@endpush
