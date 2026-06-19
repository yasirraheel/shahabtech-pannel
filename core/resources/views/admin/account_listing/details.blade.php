@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Listing Details')</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Seller')</span>
                            <h6 class="text--primary"><a href="{{ route('admin.users.detail', $accountListing->user_id) }}"><span>@</span>{{ $accountListing->user->username }}</a></h6>
                        </li>
                        @if($accountListing->status == Status::LISTING_SOLD)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Buyer')</span>
                            <h6 class="text--primary"><a href="{{ route('admin.users.detail', $accountListing->buyer_id) }}"><span>@</span>{{ $accountListing->buyer->username }}</a></h6>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Title')</span>
                            <h6>{{__($accountListing->title)}}</h6>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Category')</span>
                            <h6>{{ __($accountListing->category->name) }}</h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Social Media')</span>
                            <h6>{{ __(@$accountListing->socialMedia->name) }}</h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('ULR')</span>
                            <h6> <a href="{{ @$accountListing->url }}" target="__blank">{{ $accountListing->url }}</a> </h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Priceing Modal')</span>
                            <h6>{{ @$accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed'}}</h6>
                        </li>
               
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Sell Price')</span>
                            <h6>{{ showAmount(@$accountListing->sell_price)}} </h6>
                        </li>
                        @if($accountListing->status == Status::LISTING_SOLD)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Buy Price')</span>
                            <h6>{{ showAmount(@$accountListing->buy_price)}} </h6>
                        </li>
                        @endif
                        @if($accountListing->pricing_model == Status::AUCTION)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Minimum Price')</span>
                            <h6>{{ showAmount(@$accountListing->min_price)}} </h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Auction Last Date')</span>
                            <h6>{{ showDateTime($accountListing->auction_deadline, 'm/d/Y') }} </h6>
                        </li>
                        @endif
                       
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Status')</span>
                            <span>
                                @php echo $accountListing->statusBadge; @endphp
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            @if(!blank($accountListing->account_info))
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">@lang('Account Information')</h5>
                    <ul class="list-group list-group-flush">
                        @if ($accountListing->account_info)
                            @foreach ($accountListing->account_info as $val)
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
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">@lang('Account Credential')</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Username')</span>
                            <h6>{{ @$accountListing->accountCredential->username }}</h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Email')</span>
                            <h6>{{ @$accountListing->accountCredential->email }}</h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Password')</span>
                            <h6>{{ @$accountListing->accountCredential->password }}</h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Mobile Number')</span>
                            <h6>{{ @$accountListing->accountCredential->mobile_number }}</h6>
                        </li>
                        <li class="list-group-item">
                            <span class="text--muted">@lang('Others Information')</span>
                            <h6>{{ @$accountListing->accountCredential->others_info }}</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 mt-md-0 mt-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Thumbnail Image')</h5>
                    @if($accountListing->thumbnail_image)
                    <a class="gallery-thumb" href="{{ getImage(getFilePath('account_listing_thumb') . '/' . $accountListing->thumbnail_image, getFileSize('account_listing_thumb')) }}">
                        <img class="thumb_image" src="{{ getImage(getFilePath('account_listing_thumb') . '/' . $accountListing->thumbnail_image, getFileSize('account_listing_thumb')) }}">
                    </a>
                    @else
                    <h6 class="text-center py-5">@lang('No thumbnail uploaded yet')</h6>
                    @endif
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">@lang('Images/Screenshots')</h5>
                    <div class="row px-2">
                        @forelse ($accountListing->images as $image)
                            <div class="col-6 col-md-6 col-xl-4 px-1 py-1">
                                <a class="gallery-thumb" href="{{ getImage(getFilePath('account_listing_images') . '/' . $image->name, getFileSize('account_listing_images')) }}">
                                    <img class="images_screenshort" src="{{ getImage(getFilePath('account_listing_images') . '/' . $image->name, getFileSize('account_listing_images')) }}">
                                </a>
                            </div>
                        @empty
                        <h6 class="text-center py-5">@lang('No screenshorts uploaded yet')</h6>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectStatusModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span>@lang('Reject account listing')</span>
                    </h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.account.listing.reject.status', $accountListing->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Reason')</label>
                            <textarea class="form-control" name="reason" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary h-45 w-100" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectReasonModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span>@lang('Reason for Rejection')</span>
                    </h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="reson-box"></p>

                </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
@if ($accountListing->status == Status::LISTING_PENDING)
<button class="btn btn-outline--success confirmationBtn" data-action="{{ route('admin.account.listing.approve.status', $accountListing->id) }}" data-question="@lang('Are you sure to approve this account listing')?" type="button">
    <i class="la la-check"></i>@lang('Approve')
</button>
@endif

@if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_PENDING)
<button class="btn btn-outline--danger" data-bs-toggle="modal" data-bs-target="#rejectStatusModal" type="button">
        <i class="la la-times"></i>@lang('Reject')
    </button>
    @endif
    
    <x-back route="{{ route('admin.account.listing.index') }}" />
   
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

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.reasonBtn').on('click', function() {
                var modal = $('#rejectReasonModal');
                modal.find('.reson-box').html(($(this).data('reason')));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
