@php
    $brandElements = getContent('brand.element');
@endphp
@if (!blank($brandElements))
    <div class="client-section py-60">
        <div class="container-fluid">
            <div class="client-logos client-slider">
                @foreach ($brandElements as $brandElement)
                    <img src="{{ frontendImage('brand', @$brandElement->data_values->image, '235x65') }}" alt="brand">
                @endforeach
            </div>
        </div>
    </div>


    


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
                $('.client-slider').slick({
                    arrows: false,
                    infinite: true,
                    slidesToShow: 7,
                    slidesToScroll: 1,
                    speed: 2000,
                    cssEase: "linear",
                    autoplay: true,
                    autoplaySpeed: 0,
                    adaptiveHeight: false,
                    autoplay: true,
                    pauseOnDotsHover: false,
                    pauseOnHover: true,
                    pauseOnFocus: true,
                    responsive: [{
                            breakpoint: 1199,
                            settings: {
                                slidesToShow: 6,
                            }
                        },
                        {
                            breakpoint: 991,
                            settings: {
                                slidesToShow: 5
                            }
                        },
                        {
                            breakpoint: 767,
                            settings: {
                                slidesToShow: 4
                            }
                        },
                        {
                            breakpoint: 400,
                            settings: {
                                slidesToShow: 2
                            }
                        }
                    ]
                });

            })(jQuery);
        </script>
    @endpush

@endif
