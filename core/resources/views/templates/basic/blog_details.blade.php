@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="blog-detial-section py-60">
        <div class="container">
            <div class="row gy-5 justify-content-center">
                <div class="col-xl-9 col-lg-8">
                    <div class="blog-details">
                        <div class="blog-details__thumb">
                            <img class="fit-image" src="{{ getImage('assets/images/frontend/blog/' . $blog->data_values->image, '840x640') }}" alt="Blog image">
                        </div>
                        <div class="blog-details__content">
                            <h3 class="blog-details__title"> {{ __($blog->data_values->title) }} </h3>
                            <div class="blog-details__desc">
                                @php echo $blog->data_values->description; @endphp
                            </div>

                            <div class="fb-comments" data-href="{{ url()->current() }}" data-numposts="5"></div>

                        </div>
                        <div class="blog-details__share d-flex align-items-center mt-4 flex-wrap">
                            <ul class="social-list">
                                <li class="social-list__item">
                                    <a class="social-list__link flex-center facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                </li>
                                <li class="social-list__item">
                                    <a class="social-list__link flex-center twitter" href="https://twitter.com/intent/tweet?text={{ __($blog->data_values->title) }}&amp;url={{ urlencode(url()->current()) }}" target="_blank">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                </li>
                                <li class="social-list__item">
                                    <a class="social-list__link flex-center linkedin" href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}&amp;title={{ __($blog->data_values->title) }}&amp;summary=@php echo strLimit(strip_tags($blog->data_values->description),100) @endphp" target="_blank">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                </li>
                                <li class="social-list__item">
                                    <a class="social-list__link flex-center instagram" href="https://www.instagram.com/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4">
                    <div class="blog-sidebar-wrapper">

                        <div class="blog-sidebar">
                            <h5 class="blog-sidebar__title"> @lang('Latest Blog') </h5>
                            @foreach ($latests as $blog)
                                <div class="latest-blog">
                                    <div class="latest-blog__thumb">
                                        <a href="{{ route('blog.details',$blog->slug) }}"> <img class="fit-image" src="{{ getImage('assets/images/frontend/blog/thumb_' . @$blog->data_values->image, '420x320') }}" alt="blog image"></a>
                                    </div>
                                    <div class="latest-blog__content">
                                        <h6 class="latest-blog__title"><a href="{{ route('blog.details',$blog->slug) }}">{{ __($blog->data_values->title) }}</a></h6>
                                        <span class="latest-blog__date fs-13"> {{ showDateTime($blog->created_at, 'd M Y') }} </span>
                                    </div>
                                </div>
                            @endforeach

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('fbComment')
    @php echo loadExtension('fb-comment') @endphp
@endpush
