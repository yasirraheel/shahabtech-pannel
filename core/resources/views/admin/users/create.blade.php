@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-12">
            <div class="card mt-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Create New User')</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('admin.users.store')}}" method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('First Name')</label>
                                    <input class="form-control" type="text" name="firstname" required value="{{ old('firstname') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">@lang('Last Name')</label>
                                    <input class="form-control" type="text" name="lastname" required value="{{ old('lastname') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Email') </label>
                                    <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Username') </label>
                                    <input class="form-control" type="text" name="username" value="{{ old('username') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Password') </label>
                                    <input class="form-control" type="password" name="password" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Country') <span class="text--danger">*</span></label>
                                    <select name="country" class="form-control select2" id="country">
                                        @foreach($countries as $key => $country)
                                            <option data-mobile_code="{{ $country->dial_code }}" value="{{ $key }}" @selected(old('country') == $key)>{{ __($country->country) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Mobile Number') </label>
                                    <div class="input-group ">
                                        <span class="input-group-text mobile-code"></span>
                                        <input type="number" name="mobile" value="{{ old('mobile') }}" id="mobile" class="form-control checkUser" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Assign Plan')</label>
                                    <select name="plan_id" class="form-control select2">
                                        <option value="">@lang('No Plan Assigned')</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}">{{ __($plan->name) }} ({{ showAmount($plan->price) }} {{ gs('cur_text') }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">@lang('Selecting a plan will give the user access to all accounts under this plan.')</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Assign Specific Account')</label>
                                    <select name="account_id" class="form-control select2">
                                        <option value="">@lang('No Specific Account Assigned')</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ __(@$account->socialMedia->name) }} - {{ __($account->title) }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">@lang('Assign a specific account if you do not want to give them a full plan.')</small>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function ($) {
            "use strict";
            $('select[name=country]').on('change', function () {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
            });
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
        })(jQuery);
    </script>
@endpush
