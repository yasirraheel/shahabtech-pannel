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
                        @include($activeTemplate . 'partials.user_profile_topbar',['profileMessage' => 'Explore your account details below'])
                        <div class="row">
                            <div class="col-lg-3 col-md-4">
                                @include($activeTemplate . 'partials.user_profile_sidenav')
                            </div>
                            <div class="col-lg-8 col-md-8">
                                @php
                                 $user=auth()->user();
                                @endphp
                                <div class="profile-setting__body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex  align-items-center flex-wrap justify-content-between">
                                            <span class="text-muted">@lang('Username')</span>
                                            <span> {{ __($user->username)}} </span>
                                        </li>
                                        <li class="list-group-item d-flex  align-items-center flex-wrap justify-content-between">
                                            <span class="text-muted">@lang('Email')</span>
                                            <span> {{ $user->email }} </span>
                                        </li>
                                        <li class="list-group-item d-flex  align-items-center flex-wrap justify-content-between">
                                            <span class="text-muted">@lang('Mobile')</span>
                                            <span> {{ $user->mobile }} </span>
                                        </li>
                                        <li class="list-group-item d-flex  align-items-center flex-wrap justify-content-between">
                                            <span class="text-muted">@lang('Address')</span>
                                            <span> {{ __(@$user->address)}} </span>
                                        </li>
                                        <li class="list-group-item d-flex  align-items-center flex-wrap justify-content-between">
                                            <span class="text-muted">@lang('City')</span>
                                            <span> {{ __(@$user->city)}} </span>
                                        </li>
                                        <li class="list-group-item d-flex  align-items-center flex-wrap justify-content-between">
                                            <span class="text-muted">@lang('Country')</span>
                                            <span> {{ __(@$user->country_name)}} </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
