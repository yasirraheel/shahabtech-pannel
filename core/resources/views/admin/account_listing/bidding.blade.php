@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Bidding Amount') </th>
                                    <th> @lang('Date Time') </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($biddingListings as $biddingListing)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.users.detail', @$biddingListing->user->id) }}"><span>@</span>{{ @$biddingListing->user->username }}</a>
                                           
                                        </td>
                                        <td>
                                            {{ gs('cur_sym') }}{{ showAmount($biddingListing->amount) }}
                                        </td>
                                        <td>
                                            {{ showDateTime($biddingListing->created_at) }}
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
                @if ($biddingListings->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($biddingListings) }}
                    </div>
                @endif
            </div>
        </div>
    </div>


@endsection


@push('breadcrumb-plugins')
    <x-search-form />
@endpush
