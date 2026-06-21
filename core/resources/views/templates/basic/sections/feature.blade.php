@php
    $sellAccountContent = getContent('feature.content', true);
    $sellAccountsElements = getContent('feature.element', limit: 6);
@endphp

@if (!blank($sellAccountContent))
<div class="account-sell-section py-120 section-bg-two">
    <div class="container">
        <div class="row">
            <div class="col-lg-10">
                <div class="section-heading style-left">
                    <span class="section-heading__subtitle"> {{ __(@$sellAccountContent->data_values->title) }} </span>
                    <h3 class="section-heading__title">{{ __(@$sellAccountContent->data_values->heading) }}</h3>
                </div>
            </div>
        </div>
        <div class="row gy-4">
            @foreach ($sellAccountsElements as $sellAccountElement)
                <div class="col-lg-4 col-md-6">
                    <div class="why-choose__item">
                        <div class="why-choose__icon">
                            <img src="{{ frontendImage('feature' , @$sellAccountElement->data_values->icon_images, '50x50') }}" alt="User">
                        </div>
                        <h4 class="why-choose__title text--base"> {{ __(@$sellAccountElement->data_values->title) }}
                        </h4>
                        <p class="why-choose__desc"> {{ __(@$sellAccountElement->data_values->subtitle) }} </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
