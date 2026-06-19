@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-section py-120">
        <div class="container">
            <div class="notice"></div>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    @php
                        $kyc = getContent('kyc.content', true);
                    @endphp
                    @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
                        <div class="card custom--card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between">
                                    <h4 class="alert-heading">@lang('KYC Documents Rejected')</h4>
                                    <button class="btn btn--base btn-sm" data-bs-toggle="modal" data-bs-target="#kycRejectionReason">@lang('Show Reason')</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>{{ __(@$kyc->data_values->reject) }} <a href="{{ route('user.kyc.form') }}">@lang('Click Here to Re-submit Documents')</a>.</p>
                                <br>
                                <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a>
                            </div>
                        </div>
                    @elseif(auth()->user()->kv == Status::KYC_UNVERIFIED)
                        <div class="card custom--card mb-4">
                            <div class="card-header">
                                <h5 class="alert-heading m-0">@lang('KYC Verification required')</h5>
                            </div>
                            <div class="card-body">
                                <p>{{ __(@$kyc->data_values->required) }} <a href="{{ route('user.kyc.form') }}">@lang('Click Here to Submit Documents')</a></p>
                            </div>

                        </div>
                    @elseif(auth()->user()->kv == Status::KYC_PENDING)
                        <div class="card custom--card mb-4">
                            <div class="card-header">
                                <h4 class="alert-heading">@lang('KYC Verification pending')</h4>
                            </div>
                            <div class="card-body">
                                <p>{{ __(@$kyc->data_values->pending) }} <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
                <div class="modal custom--modal fade" id="kycRejectionReason">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                                <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>{{ auth()->user()->kyc_rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row gy-4">
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-item">
                        <div class="dashboard-item__content">
                            <a class="dashboard-item__title" href="{{ route('user.transactions') }}"> @lang('Current Balance') </a>
                            <h3 class="dashboard-item__currency"> {{ showAmount($user->balance) }} </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="fas fa-dollar-sign"></i> </span>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-item">
                        <div class="dashboard-item__content">
                            <a class="dashboard-item__title" href="{{ route('user.account.listing.index') }}">
                                @lang('Total Listings') </a>
                            <h3 class="dashboard-item__currency"> {{ $totalListingCount }} </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="fas fa-file-alt"></i> </span>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-item">
                        <div class="dashboard-item__content">
                            <a class="dashboard-item__title" href="{{ route('user.account.listing.purchase') }}">
                                @lang('Total Purchase Account') </a>
                            <h3 class="dashboard-item__currency"> {{ $purchaseAccountsCount }} </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="fas fa-list"></i></span>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-item">
                        <div class="dashboard-item__content">
                            <a class="dashboard-item__title" href="{{ route('user.account.listing.my.bid') }}">
                                @lang('Total Bid')
                            </a>
                            <h3 class="dashboard-item__currency"> {{ $bidCount }} </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="fab fa-buromobelexperte"></i> </span>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-item">
                        <div class="dashboard-item__content">
                            <a class="dashboard-item__title" href="{{ route('user.deposit.history') }}"> @lang('Total Deposit')
                            </a>
                            <h3 class="dashboard-item__currency"> {{ showAmount($totalDeposit) }} </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="menu-icon las la-file-invoice-dollar"></i> </span>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-item">
                        <div class="dashboard-item__content">
                            <a class="dashboard-item__title" href="{{ route('user.withdraw') }}"> @lang('Total Withdrow') </a>
                            <h3 class="dashboard-item__currency"> {{ showAmount($totalWithdrawals) }}
                            </h3>
                        </div>
                        <span class="dashboard-item__icon"> <i class="menu-icon la la-bank"></i></span>
                    </div>
                </div>
            </div>
            <div class="dashboard-body">
                <div class="row gy-4">
                    <div class="col-xl-12">
                        <h5 class="mb-2">@lang('Active Bids')</h5>
                        <div class="card custom--card">
                            <div class="card-body p-0">
                                @include($activeTemplate . 'user.account_listings.listing_table', ['biddings' => $activeBids])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-confirmation-modal addClass="custom--modal" :customButton=true />
@endsection
