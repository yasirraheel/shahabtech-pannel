@extends('admin.layouts.app')
@section('panel')
    @php
        $statusIsShow = request()->routeIs('admin.account.listing.index');
    @endphp
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-body p-0">
                    <div class="table-responsive--lg table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Title|Image')</th>
                                    <th>@lang('Social Media') </th>
                                    <th> @lang('Category') </th>
                                    <th>@lang('Pricing Model | Sell Price')</th>
                                    <th>@lang('Biddings')</th>
                                    @if ($statusIsShow)
                                        <th>@lang('Status')</th>
                                    @endif
                                    <th>@lang('Report')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountListings as $accountListing)
                                    <tr>
                                        <td>
                                            <p class="m-0">{{ strLimit($accountListing->title, 50) }}</p>
                                            <a href="{{ route('admin.users.detail', @$accountListing->user_id) }}"><span>@</span>{{ @$accountListing->user->username }}</a>
                                        </td>
                                        <td>
                                            {{ __(@$accountListing->socialMedia->name) }}
                                        </td>
                                        <td> {{ __(@$accountListing->category->name) }} </td>
                                        <td>
                                            {{ __(@$accountListing->pricing_model == Status::AUCTION ? 'Action' : 'Fixed') }}
                                            |
                                            {{ showAmount($accountListing->sell_price) }}
                                        </td>
                                        <td>
                                            @if ($accountListing->pricing_model == Status::AUCTION)
                                                <a class="badge badge--success" href="{{ route('admin.account.listing.bidding', $accountListing->id) }}">{{ $accountListing->account_bidding_count }}</a>
                                            @else
                                                @lang('N/A')
                                            @endif
                                        </td>

                                        @if ($statusIsShow)
                                            <td> @php echo $accountListing->statusBadge; @endphp </td>
                                        @endif
                                        <td>
                                            <a class="badge badge--{{ @$accountListing->report_count ? 'danger' : 'success' }}" href="{{ route('admin.account.listing.report', $accountListing->id) }}">{{ $accountListing->report_count }}</a>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.account.listing.detail', $accountListing->id) }}">
                                                <i class="las la-desktop"></i> @lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($accountListings->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($accountListings) }}
                    </div>
                @endif
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
    <form id="searchForm" method="get">
        <div class="d-flex justify-content-end flex-wrap gap-3">
            <div>
                <select class="form-control select2 search-on-change" name="category" data-minimum-results-for-search="-1">
                    <option value="">@lang('Select category')</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ __($category->name) }}
                        </option>
                    @endforeach
                </select>

            </div>
            <div>
                <select class="form-control select2 search-on-change" name="social_media" data-minimum-results-for-search="-1">
                    <option value="">@lang('Select social media')</option>
                    @foreach ($socialMedias as $socialMedia)
                        <option value="{{ $socialMedia->id }}" @selected(request('social_media') == $socialMedia->id)>
                            {{ __($socialMedia->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <div>
                    <x-search-form />
                </div>
            </div>
        </div>

    </form>

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

            $('.search-on-change').on('change', function() {
                $('#searchForm').submit();
            })

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .select2-container .select2-selection--single .select2-selection__rendered {
            padding-right: 40px !important;
        }

        .select2-container {
            min-width: 220px;
        }
    </style>
@endpush
