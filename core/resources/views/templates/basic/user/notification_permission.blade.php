@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="profile-setting-section py-120">
        <div class="container">
            <div class="row justify-content-center gy-4">
                <div class="col-12">
                    <div class="profile-filter d-md-none d-block text-end">
                        <button class="profile-filter__button toggle-profile-sidebar" type="button">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>
                </div>
                <div class="col-xl-10">
                    <div class="profile-setting">
                        @include($activeTemplate . 'partials.user_profile_topbar', ['profileMessage' => 'Manage your notification setting from the below'])
                        <div class="row">
                            <div class="col-lg-3 col-md-4">
                                @include($activeTemplate . 'partials.user_profile_sidenav')
                            </div>
                            <div class="col-lg-8 col-md-8">
                                <form method="post">
                                    @csrf
                                <div class="profile-setting__body">
                                    <div class="form--check">
                                        <input class="form-check-input" type="checkbox" value="1" name="approved" id="approved" @checked(@$notification->approved == Status::ENABLE)>
                                        <label class="form-check-label" for="approved">
                                            @lang('Approved Account')
                                        </label>
                                    </div>
                                    <div class="form--check">
                                        <input class="form-check-input" type="checkbox" name="reject" value="1" id="reject" @checked(@$notification->reject == Status::ENABLE)>
                                        <label class="form-check-label" for="reject">
                                            @lang('Rejected Account')
                                        </label>
                                    </div>
                                    <div class="form--check">
                                        <input class="form-check-input" type="checkbox" name="bid" value="1" id="bid" @checked(@$notification->bid == Status::ENABLE)>
                                        <label class="form-check-label" for="bid">
                                            @lang('Bid Account')
                                        </label>
                                    </div>
                                    <div class="form--check">
                                        <input class="form-check-input" type="checkbox" name="buy" value="1" id="buy" @checked(@$notification->buy == Status::ENABLE)>
                                        <label class="form-check-label" for="buy">
                                           @lang('Buy Account')
                                        </label>
                                    </div>
                                    <div class="form--check">
                                        <input class="form-check-input" type="checkbox" value="1" name="refund" id="refund" @checked(@$notification->refund == Status::ENABLE)>
                                        <label class="form-check-label" for="refund">
                                            @lang('Refund')
                                        </label>
                                    </div>
                                    <div class="form--check">
                                        <input class="form-check-input" type="checkbox" name="sell" value="1" id="sell" @checked(@$notification->sell == Status::ENABLE)>
                                        <label class="form-check-label" for="sell">
                                            @lang('Sell Account')
                                        </label>
                                    </div>
                                    <div class="form--check">
                                        <input class="form-check-input" type="checkbox" name="cancel_bid" value="1" id="cancel_bid" @checked(@$notification->cancel_bid == Status::ENABLE)>
                                        <label class="form-check-label" for="cancel_bid">
                                            @lang('Cancel Bid')
                                        </label>
                                    </div>
                                    
                                    <div class="profile-setting__button d-flex justify-content-end">
                                        <button class="btn btn--base " type="submit"> @lang('Save Changes') </button>

                                    </div>
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
