@php
    $testimonialElements = getContent('testimonial.element');
@endphp
@if (!blank($testimonialElements))
<section class="testimonials py-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-9">
                <div class="testimonial-slider">
                    @foreach ($testimonialElements as $testimonialElement)
                        <div class="testimonails-card">
                            <div class="testimonial-item">
                                <div class="testimonial-item__content">
                                    <div class="testimonial-item__rating">
                                        @php
                                            $review = @$testimonialElement->data_values->review;
                                            $noReview = 5 - $review;
                                        @endphp
                                        <ul class="rating-list">
                                            @for ($i = 1; $i <= $review; $i++)
                                                <li class="rating-list__item"><i class="fas fa-star"></i></li>
                                            @endfor
                                            @for ($i = 1; $i <= $noReview; $i++)
                                                <li class="rating-list__item"><i class="far fa-star"></i></li>
                                            @endfor
                                        </ul>
                                    </div>
                                    <p class="testimonial-item__desc">"{{ __(@$testimonialElement->data_values->comment) }}"</p>
                                    <div class="d-flex">
                                        <div class="testimonial-item__info">
                                            <div class="testimonial-item__thumb">
                                                <img class="fit-image" src="{{ frontendImage('testimonial', @$testimonialElement->data_values->image, '60x60') }}" alt="Testimonial">
                                            </div>
                                            <div class="testimonial-item__details">
                                                <h6 class="testimonial-item__name"> {{ __(@$testimonialElement->data_values->name) }} </h6>
                                                <span class="testimonial-item__designation"> {{ __(@$testimonialElement->data_values->designation) }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

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

            $('.testimonial-slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 2000,
                speed: 1500,
                dots: true,
                pauseOnHover: true,
                arrows: true,
                prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-long-arrow-alt-left"></i></button>',
                nextArrow: '<button type="button" class="slick-next"><i class="fas fa-long-arrow-alt-right"></i></button>',
                responsive: [{
                        breakpoint: 1199,
                        settings: {
                            slidesToShow: 1,
                            dots: true,
                        }
                    },
                    {
                        breakpoint: 991,
                        settings: {
                            arrows: false,
                            slidesToShow: 1
                        }
                    },
                    {
                        breakpoint: 767,
                        settings: {
                            arrows: false,
                            slidesToShow: 1
                        }
                    }
                ]
            });
        })(jQuery);
    </script>
@endpush
