@extends($activeTemplate . 'layouts.master')
@section('content')
    <section class="section py-120">

        <div class="container">
            <div class="card custom--card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span> @lang('Account Title') : <b>{{ $accountListing->title }}</b></span>
                        <span>@lang('Account Auction End') : <b>{{ showDateTime($accountListing->auction_deadline, 'm/d/Y') }}</b></span>
                    </div>
                </div>
            </div>

            <div class="card custom--card ">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table--responsive--lg">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Bid Amount') </th>
                                    <th> @lang('Date Time') </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($biddingListings as $biddingListing)
                                    <tr>
                                        <td>
                                            {{ @$biddingListing->user->username }}
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
    </section>
@endsection
