@php
    $howWorkContent  = getContent('how_it_work.content', true);
    $howWorkElements = getContent('how_it_work.element', orderById: true);
@endphp

@if (!blank($howWorkContent) && !blank($howWorkElements))
    <section class="how-work-section py-120 section-bg-two">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-6 pe-lg-5">
                    <div class="section-heading style-left">
                        <span class="section-heading__subtitle"> {{ __(@$howWorkContent->data_values->title) }} </span>
                        <h3 class="section-heading__title"> {{ __(@$howWorkContent->data_values->heading) }} </h3>
                        <div class="section-heading__button">
                            <a class="btn btn-outline--base" href="{{ @$howWorkContent->data_values->button_link }}"> {{ __(@$howWorkContent->data_values->button_text) }} </a>
                        </div>
                    </div>
                    <div class="how-work__thumb">
                        <img src="{{ frontendImage('how_it_work' , @$howWorkContent->data_values->image, '600x675') }}" alt="How It Work Image">
                        <div class="how-work__shape"><img src="{{ asset($activeTemplateTrue . 'images/thumbs/facebook.png') }}" alt="Facebook"></div>
                        <div class="how-work__shape-two"><img src="{{ asset($activeTemplateTrue . 'images/thumbs/heart.png') }}" alt="Heart"></div>
                        <div class="how-work__shape-three"><img src="{{ asset($activeTemplateTrue . 'images/thumbs/like.png') }}" alt="Like"></div>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <ul class="how-work">
                        @foreach ($howWorkElements as $howWorkElement)
                            <li>
                                <div class="how-work__content">
                                    <span class="how-work__icon"> @php echo @$howWorkElement->data_values->icon @endphp </span>
                                    <h4 class="how-work__title"> {{ __(@$howWorkElement->data_values->title) }} </h5>
                                    <p class="how-work__desc"> {{ __(@$howWorkElement->data_values->subtitle) }} </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endif
