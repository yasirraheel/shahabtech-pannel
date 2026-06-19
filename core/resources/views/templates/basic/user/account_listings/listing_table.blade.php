<div class="table-responsive">
    <table class="table--responsive--lg table">
        <thead>
            <tr>
                <th>@lang('Account Listing')</th>
                <th>@lang('My Bid')</th>
                <th>@lang('Highest Bid')</th>
                <th>@lang('Status')</th>
                <th>@lang('Action')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($biddings as $bidding)
                <tr>
                    <td>
                        @php
                            if (@$bidding->accountListing->status == Status::LISTING_ACTIVE) {
                                $listingLink = route('account.listing.details', [slug(@$bidding->accountListing->title), @$bidding->accountListing->id]);
                            } else {
                                $listingLink = 'javascript:void(0)';
                            }
                        @endphp
                        <a class="fs-16" href="{{ $listingLink }}">
                            {{ __(strLimit(@$bidding->accountListing->title, 20)) }}
                        </a>
                    </td>
                    <td>
                        {{ showAmount($bidding->amount) }}
                    </td>
                    <td>
                        {{ showAmount($bidding->accountListing->account_bidding_max_amount) }}
                    </td>
                    <td>
                        @php echo $bidding->accountListing->statusBadge @endphp
                    </td>
                    <td>
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                        @if (@$bidding->accountListing->status == Status::LISTING_ACTIVE)
                            <button class="btn btn--danger confirmationBtn btn--sm" data-question="@lang('Are you sure to cancel this bid?')"
                                data-action="{{ route('user.account.listing.cancel.bid', @$bidding->id) }}"
                                data-bs-toggle="tooltip" title="@lang('Cancel')">
                                <i class="las la-trash"></i> 
                            </button>
                        @endif
                        <a class="btn btn--base btn--sm @if (@$bidding->accountListing->status != Status::LISTING_ACTIVE) disabled @endif"
                            href="{{ $listingLink }}" data-bs-toggle="tooltip" title="@lang('Details')">
                            <i class="las la-desktop"></i>
                        </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-center" colspan="100%">
                        @include($activeTemplate . 'empty', [
                            'message' => $emptyMessage,
                        ])
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if ($biddings->hasPages())
        {{ paginateLinks($biddings) }}
    @endif
</div>


<x-confirmation-modal addClass="custom--modal" :customButton=true />