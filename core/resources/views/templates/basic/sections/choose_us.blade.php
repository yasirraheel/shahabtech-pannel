@php
    $chooseUsContent  = getContent('choose_us.content', true);
    $chooseUsElements = getContent('choose_us.element', limit: 3, orderById: true);
@endphp

@if (!blank($chooseUsElements))
    <div class="why-choose-section py-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-10">
                    <div class="section-heading style-left">
                        <span class="section-heading__subtitle"> {{ __(@$chooseUsContent->data_values->title) }} </span>
                        <h3 class="section-heading__title"> {{ __(@$chooseUsContent->data_values->heading) }} </h3>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center gy-4">
                @foreach ($chooseUsElements as $chooseUsElement)
                    <div class="col-lg-4 col-md-6">
                        <div class="why-choose__item">
                            <div class="why-choose__icon">
                                <img src="{{ frontendImage('choose_us', @$chooseUsElement->data_values->icon_images, '64x64') }}" alt="Choose Us">
                            </div>
                            <h4 class="why-choose__title text--base"> {{ __(@$chooseUsElement->data_values->title) }} </h4>
                            <p class="why-choose__desc"> {{ __(@$chooseUsElement->data_values->subtitle) }} </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
