@extends($activeTemplate . 'layouts.master')
@section('content')
    <section class="py-120">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="card custom--card">
                        <div class="card-body">
                            <h5 class="card-title">@lang('Listing Details')</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Seller')</span>
                                    <h6 class="text--primary">{{ $soldAccountListing->user->username }}</h6>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Title')</span>
                                    <h6>{{ __($soldAccountListing->title) }}</h6>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Category')</span>
                                    <h6>{{ __($soldAccountListing->category->name) }}</h6>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Social Media')</span>
                                    <h6>{{ __(@$soldAccountListing->socialMedia->name) }}</h6>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('ULR')</span>
                                    <h6> <a href="{{ @$soldAccountListing->url }}" target="__blank">{{ $soldAccountListing->url }}</a> </h6>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Priceing Modal')</span>
                                    <h6>{{ @$soldAccountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed' }}</h6>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Sell Price')</span>
                                    <h6>{{ gs('cur_sym') }}{{ showAmount(@$soldAccountListing->sell_price) }} </h6>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Buy Price')</span>
                                    <h6>{{ gs('cur_sym') }}{{ showAmount(@$soldAccountListing->buy_price) }} </h6>
                                </li>
                                @if ($soldAccountListing->pricing_model == Status::AUCTION)
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class="text--muted">@lang('Minimum Price')</span>
                                        <h6>{{ gs('cur_sym') }}{{ showAmount(@$soldAccountListing->min_price) }} </h6>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class="text--muted">@lang('Auction Last Date')</span>
                                        <h6>{{ showDateTime($soldAccountListing->auction_deadline, 'm/d/Y') }} </h6>
                                    </li>
                                @endif

                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Status')</span>
                                    <span>
                                        @php echo $soldAccountListing->statusBadge; @endphp
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if(!blank($soldAccountListing->account_info))
                    <div class="card custom--card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">@lang('Account Information')</h5>
                            <ul class="list-group list-group-flush">
                                @if ($soldAccountListing->account_info)
                                    @foreach ($soldAccountListing->account_info as $val)
                                        @continue(!$val->value)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ __($val->name) }}
                                            <span>
                                                @if ($val->type == 'checkbox')
                                                    {{ implode(',', $val->value) }}
                                                @elseif($val->type == 'file')
                                                    <a class="me-3" href="{{ route('user.attachment.download', encrypt(getFilePath('verify') . '/' . $val->value)) }}"><i class="fa fa-file"></i> @lang('Attachment') </a>
                                                @else
                                                    <span class="fw-bold">{{ __($val->value) }}</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    @endif

                    <div class="card custom--card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">@lang('Account Credential')</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Username')</span>
                                    <h6>{{ @$soldAccountListing->accountCredential->username }}</h6>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Email')</span>
                                    <h6>{{ @$soldAccountListing->accountCredential->email }}</h6>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Password')</span>
                                    <h6>{{ @$soldAccountListing->accountCredential->password }}</h6>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Mobile Number')</span>
                                    <h6>{{ @$soldAccountListing->accountCredential->mobile_number }}</h6>
                                </li>
                                <li class="list-group-item">
                                    <span class="text--muted">@lang('Others Information')</span>
                                    <h6>{{ @$soldAccountListing->accountCredential->others_info }}</h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mt-md-0 mt-3">
                    <div class="card custom--card">
                        <div class="card-body">
                            <h5 class="card-title">@lang('Thumbnail Image')</h5>
                            @if ($soldAccountListing->thumbnail_image)
                                <a class="gallery-thumb" href="{{ getImage(getFilePath('account_listing_thumb') . '/' . $soldAccountListing->thumbnail_image, getFileSize('account_listing_thumb')) }}">
                                    <img class="thumb_image" src="{{ getImage(getFilePath('account_listing_thumb') . '/' . $soldAccountListing->thumbnail_image, getFileSize('account_listing_thumb')) }}">
                                </a>
                            @else
                                <h6 class="py-5 text-center">@lang('No thumbnail uploaded yet')</h6>
                            @endif
                        </div>
                    </div>
                    <div class="card custom--card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">@lang('Images/Screenshots')</h5>
                            <div class="row px-2">
                                @forelse ($soldAccountListing->images as $image)
                                    <div class="col-6 col-md-6 col-xl-4 px-1 py-1">
                                        <a class="gallery-thumb" href="{{ getImage(getFilePath('account_listing_images') . '/' . $image->name, getFileSize('account_listing_images')) }}">
                                            <img class="images_screenshort" src="{{ getImage(getFilePath('account_listing_images') . '/' . $image->name, getFileSize('account_listing_images')) }}">
                                        </a>
                                    </div>
                                @empty
                                    <h6 class="py-5 text-center">@lang('No screenshorts uploaded yet')</h6>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('style-lib')
    <link href="{{ asset('assets/global/css/magnific-popup.css') }}" rel="stylesheet">
@endpush
@push('script-lib')
    <script src="{{ asset('assets/global/js/magnific-popup.js') }}"></script>
@endpush

@push('script')
    <script>
        $('.gallery-thumb').magnificPopup({
            type: 'image',
            gallery: {
                enabled: true
            }
        });
    </script>
@endpush

@push('style')
    <style>
        .thumb_image {
            max-width: 400px;
        }

        .images_screenshort {
            width: 100%;
        }
    </style>
@endpush
