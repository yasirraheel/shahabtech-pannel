@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="blog py-120">
        <div class="container">
            <div class="row gy-5 justify-content-center">
                @foreach ($blogElements as $blogElement)
                    <div class="col-lg-4 col-sm-6 col-xsm-6">
                        <div class="blog-item">
                            <div class="blog-item__thumb">
                                <a class="blog-item__thumb-link" href="{{ route('blog.details', [slug(@$blogElement->slug)]) }}">
                                    <img class="fit-image" src="{{ getImage('assets/images/frontend/blog/thumb_' . $blogElement->data_values->image, '420x320') }}" alt="image">
                                </a>
                            </div>
                            <div class="blog-item__content">
                                <span class="blog-item__category"> <i class="far fa-clock"></i> {{ showDateTime(@$blogElement->created_at, 'd M Y') }} </span>
                                <h4 class="blog-item__title text--base"><a class="blog-item__title-link border-effect" href="{{ route('blog.details', [slug(@$blogElement->slug)]) }}"> {{ __(@$blogElement->data_values->title) }} </a></h4>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($blogElements->hasPages())
            {{ paginateLinks($blogElements) }}
        @endif
    </section>

    @if (@$sections->secs != null)
        @foreach (json_decode($sections->secs) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection
