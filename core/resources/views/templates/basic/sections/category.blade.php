@php
    $categortyContent  = @getContent('category.content',true)->data_values;
    $categories        = App\Models\Category::withCount('accountListing')->active()->orderBy('account_listing_count','desc')->take(8)->get();
@endphp
@if (!blank($categortyContent))
<section class="category-main py-120">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-heading">
                    <span class="section-heading__subtitle"> {{ __(@$categortyContent->title)}}
                    </span>
                    <h3 class="section-heading__title"> {{ __(@$categortyContent->heading)}}</h3>
                </div>
            </div>
        </div>
        <div class="row gy-3 justify-content-center">
            @foreach ($categories as $category)
            <a href="{{route('buy.account')}}?category_id={{$category->id}}" class="cate-outer">
                <div class="category-item text-center">
                    <div class="category-thumb">
                        <img src="{{ getImage(getFilePath('category') . '/' . $category->image, getFileSize('category')) }}" alt="category Image">
                    </div>
                    <h6 class="fs--15px cate-title">{{ __(@$category->name)}}</h6>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
