@php
    $blogContent = getContent('blog.content', true);
    $blogElements = getContent('blog.element', limit: 3);
@endphp

@if (!blank($blogContent) && !blank($blogElements))
    <section class="blog py-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading style-left">
                        <span class="section-heading__subtitle"> {{ __(@$blogContent->data_values->title) }} </span>
                        <h3 class="section-heading__title"> {{ __(@$blogContent->data_values->heading) }} </h3>
                    </div>
                </div>
            </div>
            <div class="row gy-4 justify-content-center">
                @foreach ($blogElements as $blogElement)
                    <div class="col-lg-4 col-sm-6 col-xsm-6">
                        <div class="blog-item">
                            <div class="blog-item__thumb">
                                <a class="blog-item__thumb-link" href="{{ route('blog.details', $blogElement->slug) }}">
                                    <img class="fit-image" src="{{ frontendImage('blog', 'thumb_' . $blogElement->data_values->image, '420x320') }}" alt="blog">
                                </a>
                            </div>
                            <div class="blog-item__content">
                                <span class="blog-item__category"> <i class="far fa-clock"></i>
                                    {{ showDateTime(@$blogElement->created_at, 'd M Y') }} </span>
                                <h4 class="blog-item__title">
                                    <a class="blog-item__title-link border-effect" href="{{ route('blog.details', $blogElement->slug) }}">
                                        {{ __(@$blogElement->data_values->title) }} </a>
                                </h4>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
