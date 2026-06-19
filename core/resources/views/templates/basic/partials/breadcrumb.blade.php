@php
    $breadcrumbContent=getContent("breadcrumb.content",true);
@endphp
<section class="breadcrumb"
    style="background-image: url('{{ getImage('assets/images/frontend/breadcrumb/' . @$breadcrumbContent->data_values->background_image, '1920x300') }}')">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="breadcrumb__wrapper">
                    <h2 class="breadcrumb__title mb-0">
                        {{ __($pageTitle) }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
</section>
