@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-12">
            <div class="row gy-4">

                <div class="col-xxl-3 col-sm-6">
                    <x-widget
                        style="7"
                        link="{{ route('admin.report.transaction',$user->id) }}"
                        title="Balance"
                        icon="las la-money-bill-wave-alt"
                        value="{{ showAmount($user->balance) }}"
                        bg="indigo"
                        type="2"
                    />
                </div>


                <div class="col-xxl-3 col-sm-6">
                    <x-widget
                        style="7"
                        link="{{ route('admin.deposit.list',$user->id) }}"
                        title="Deposits"
                        icon="las la-wallet"
                        value="{{ showAmount($totalDeposit) }}"
                        bg="8"
                        type="2"
                    />
                </div>

                <div class="col-xxl-3 col-sm-6">
                    <x-widget
                        style="7"
                        link="{{ route('admin.withdraw.data.all',$user->id) }}"
                        title="Withdrawals"
                        icon="la la-bank"
                        value="{{ showAmount($totalWithdrawals) }}"
                        bg="6"
                        type="2"
                    />
                </div>

                <div class="col-xxl-3 col-sm-6">
                    <x-widget
                        style="7"
                        link="{{ route('admin.report.transaction',$user->id) }}"
                        title="Transactions"
                        icon="las la-exchange-alt"
                        value="{{ $totalTransaction }}"
                        bg="17"
                        type="2"
                    />
                </div>


            </div>

            <div class="d-flex flex-wrap gap-3 mt-4">
                <div class="flex-fill">
                    <button data-bs-toggle="modal" data-bs-target="#addSubModal" class="btn btn--success btn--shadow w-100 btn-lg bal-btn" data-act="add">
                        <i class="las la-plus-circle"></i> @lang('Balance')
                    </button>
                </div>

                <div class="flex-fill">
                    <button data-bs-toggle="modal" data-bs-target="#addSubModal" class="btn btn--danger btn--shadow w-100 btn-lg bal-btn" data-act="sub">
                        <i class="las la-minus-circle"></i> @lang('Balance')
                    </button>
                </div>

                <div class="flex-fill">
                    <a href="{{route('admin.report.login.history')}}?search={{ $user->username }}" class="btn btn--primary btn--shadow w-100 btn-lg">
                        <i class="las la-list-alt"></i>@lang('Logins')
                    </a>
                </div>

                <div class="flex-fill">
                    <a href="{{ route('admin.users.notification.log',$user->id) }}" class="btn btn--secondary btn--shadow w-100 btn-lg">
                        <i class="las la-bell"></i>@lang('Notifications')
                    </a>
                </div>

                @if($user->kyc_data)
                <div class="flex-fill">
                    <a href="{{ route('admin.users.kyc.details', $user->id) }}" target="_blank" class="btn btn--dark btn--shadow w-100 btn-lg">
                        <i class="las la-user-check"></i>@lang('KYC Data')
                    </a>
                </div>
                @endif

                <div class="flex-fill">
                    @if($user->status == Status::USER_ACTIVE)
                    <button type="button" class="btn btn--warning btn--shadow w-100 btn-lg userStatus" data-bs-toggle="modal" data-bs-target="#userStatusModal">
                        <i class="las la-ban"></i>@lang('Ban User')
                    </button>
                    @else
                    <button type="button" class="btn btn--success btn--shadow w-100 btn-lg userStatus" data-bs-toggle="modal" data-bs-target="#userStatusModal">
                        <i class="las la-undo"></i>@lang('Unban User')
                    </button>
                    @endif
                </div>

                <div class="flex-fill">
                    <button type="button" class="btn btn--danger btn--shadow w-100 btn-lg" data-bs-toggle="modal" data-bs-target="#userLogoutModal">
                        <i class="las la-sign-out-alt"></i>@lang('Logout User')
                    </button>
                </div>
            </div>

            <div class="card mt-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Information of') {{$user->fullname}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('admin.users.update',[$user->id])}}" method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('First Name')</label>
                                    <input class="form-control" type="text" name="firstname" required value="{{$user->firstname}}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">@lang('Last Name')</label>
                                    <input class="form-control" type="text" name="lastname" required value="{{$user->lastname}}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Email') </label>
                                    <input class="form-control" type="email" name="email" value="{{$user->email}}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Mobile Number') </label>
                                    <div class="input-group ">
                                        <span class="input-group-text mobile-code">+{{ $user->dial_code }}</span>
                                        <input type="number" name="mobile" value="{{ $user->mobile }}" id="mobile" class="form-control checkUser" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label>@lang('Address')</label>
                                    <input class="form-control" type="text" name="address" value="{{@$user->address}}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Reset Password')</label>
                                    <div class="input-group">
                                        <input class="form-control" type="password" name="password" id="reset-password-field" placeholder="@lang('Leave blank to keep current password')">
                                        <button class="btn btn-outline-secondary" type="button" id="generate-password-btn" title="@lang('Generate Random Password')"><i class="las la-dice"></i></button>
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-password-btn" title="@lang('Show/Hide Password')"><i class="las la-eye"></i></button>
                                        <button class="btn btn-outline-secondary" type="button" id="copy-password-btn" title="@lang('Copy Password')"><i class="las la-copy"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label>@lang('City')</label>
                                    <input class="form-control" type="text" name="city" value="{{@$user->city}}">
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="form-group ">
                                    <label>@lang('State')</label>
                                    <input class="form-control" type="text" name="state" value="{{@$user->state}}">
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="form-group ">
                                    <label>@lang('Zip/Postal')</label>
                                    <input class="form-control" type="text" name="zip" value="{{@$user->zip}}">
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="form-group ">
                                    <label>@lang('Country') <span class="text--danger">*</span></label>
                                    <select name="country" class="form-control select2">
                                        @foreach($countries as $key => $country)
                                            <option data-mobile_code="{{ $country->dial_code }}" value="{{ $key }}" @selected($user->country_code == $key)>{{ __($country->country) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Assign Plan')</label>
                                    <select name="plan_id" class="form-control select2">
                                        <option value="">@lang('No Plan Assigned')</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" @selected($user->plan_id == $plan->id)>{{ __($plan->name) }} ({{ showAmount($plan->price) }} {{ gs('cur_text') }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">@lang('Selecting a plan will give the user access to all accounts under this plan.')</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Account Type')</label>
                                    <div class="d-flex align-items-center mt-2">
                                        <span class="me-3">@lang('Standard')</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="is_trial" name="is_trial" @if($user->is_trial) checked @endif>
                                            <label class="form-check-label" for="is_trial">@lang('Trial Period')</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" id="standard-expiry-wrapper" style="{{ $user->is_trial ? 'display:none;' : '' }}">
                                <div class="form-group">
                                    <label>@lang('Subscription Expiry Date')</label>
                                    <input class="form-control" type="datetime-local" name="expires_at" value="{{ $user->expires_at ? $user->expires_at->format('Y-m-d\TH:i') : $user->created_at->addDays(30)->format('Y-m-d\TH:i') }}">
                                    <small class="text-muted">@lang('By default, users expire 30 days after their creation date.')</small>
                                </div>
                            </div>

                            <div class="col-md-12" id="trial-period-wrapper" style="{{ $user->is_trial ? '' : 'display:none;' }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Trial Start Mode')</label>
                                            <select name="trial_start_type" class="form-control" id="trial_start_type">
                                                <option value="immediate" @selected($user->is_trial && !$user->pending_trial_minutes)>@lang('Start Immediately (or Already Started)')</option>
                                                <option value="next_login" @selected($user->is_trial && $user->pending_trial_minutes > 0)>@lang('Start on User\'s Next Login')</option>
                                            </select>
                                            @if($user->is_trial && $user->expires_at && !$user->pending_trial_minutes)
                                                <div class="mt-1 text-warning"><small><i class="las la-info-circle"></i> @lang('Trial is currently active and expires at:') {{ showDateTime($user->expires_at) }}</small></div>
                                                <div class="mt-1 text-muted"><small>@lang('To extend the active trial, just change the expiry date in the standard mode.')</small></div>
                                            @endif
                                            @if($user->is_trial && $user->pending_trial_minutes > 0)
                                                <div class="mt-1 text-warning"><small><i class="las la-info-circle"></i> @lang('Trial is currently pending to start. Duration:') {{ $user->pending_trial_minutes }} @lang('Minutes')</small></div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="trial-duration-wrapper">
                                        <div class="form-group">
                                            <label>@lang('Set New Trial Duration')</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="trial_duration" placeholder="e.g. 2" min="1">
                                                <select name="trial_unit" class="form-control">
                                                    <option value="minutes">@lang('Minutes')</option>
                                                    <option value="hours" selected>@lang('Hours')</option>
                                                    <option value="days">@lang('Days')</option>
                                                </select>
                                            </div>
                                            <small class="text-muted">@lang('Fill this only if you want to assign/override the trial duration.')</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Assign Specific Accounts')</label>
                                    <select name="account_ids[]" class="form-control select2" multiple="multiple" id="account-selector">
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" data-name="{{ __(@$account->socialMedia->name) }} - {{ __($account->title) }}" @selected(in_array($account->id, $user->account_ids ?? []))>{{ __(@$account->socialMedia->name) }} - {{ __($account->title) }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">@lang('Assign specific accounts if you do not want to give them a full plan. You can select multiple.')</small>
                                </div>
                                <div id="account-prices-container" class="mt-2"></div>
                            </div>


                            <div class="col-xl-3 col-md-6 col-12">
                                <div class="form-group">
                                    <label>@lang('Email Verification')</label>
                                    <input type="checkbox" data-width="100%" data-onstyle="-success" data-offstyle="-danger"
                                           data-bs-toggle="toggle" data-on="@lang('Verified')" data-off="@lang('Unverified')" name="ev"
                                           @if($user->ev) checked @endif>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 col-12">
                                <div class="form-group">
                                    <label>@lang('Mobile Verification')</label>
                                    <input type="checkbox" data-width="100%" data-onstyle="-success" data-offstyle="-danger"
                                           data-bs-toggle="toggle" data-on="@lang('Verified')" data-off="@lang('Unverified')" name="sv"
                                           @if($user->sv) checked @endif>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md- col-12">
                                <div class="form-group">
                                    <label>@lang('2FA Verification') </label>
                                    <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Enable')" data-off="@lang('Disable')" name="ts" @if($user->ts) checked @endif>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md- col-12">
                                <div class="form-group">
                                    <label>@lang('KYC') </label>
                                    <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Verified')" data-off="@lang('Unverified')" name="kv" @if($user->kv == Status::KYC_VERIFIED) checked @endif>
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



    {{-- Add Sub Balance MODAL --}}
    <div id="addSubModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span class="type"></span> <span>@lang('Balance')</span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{route('admin.users.add.sub.balance',$user->id)}}" class="balanceAddSub disableSubmission" method="POST">
                    @csrf
                    <input type="hidden" name="act">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="amount" class="form-control" placeholder="@lang('Please provide positive amount')" required>
                                <div class="input-group-text">{{ __(gs('cur_text')) }}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Remark')</label>
                            <textarea class="form-control" placeholder="@lang('Remark')" name="remark" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="userStatusModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($user->status == Status::USER_ACTIVE) @lang('Ban User') @else @lang('Unban User') @endif
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{route('admin.users.status',$user->id)}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($user->status == Status::USER_ACTIVE)
                        <h6 class="mb-2">@lang('If you ban this user he/she won\'t able to access his/her dashboard.')</h6>
                        <div class="form-group">
                            <label>@lang('Reason')</label>
                            <textarea class="form-control" name="reason" rows="4" required></textarea>
                        </div>
                        @else
                        <p><span>@lang('Ban reason was'):</span></p>
                        <p>{{ $user->ban_reason }}</p>
                        <h4 class="text-center mt-3">@lang('Are you sure to unban this user?')</h4>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($user->status == Status::USER_ACTIVE)
                        <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                        @else
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                        <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="userLogoutModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Logout User')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{route('admin.users.logout', $user->id)}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>@lang('Are you sure you want to log out this user remotely?')</p>
                        <p>@lang('This will terminate their active session instantly.')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                        <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{route('admin.users.login',$user->id)}}" target="_blank" class="btn btn-sm btn-outline--primary" ><i class="las la-sign-in-alt"></i>@lang('Login as User')</a>
@endpush

@push('script')
<script>
    (function($){
    "use strict"

        $('#is_trial').on('change', function() {
            if($(this).is(':checked')) {
                $('#standard-expiry-wrapper').hide();
                $('#trial-period-wrapper').show();
            } else {
                $('#standard-expiry-wrapper').show();
                $('#trial-period-wrapper').hide();
            }
        });

        $('.bal-btn').on('click',function(){

            $('.balanceAddSub')[0].reset();

            var act = $(this).data('act');
            $('#addSubModal').find('input[name=act]').val(act);
            if (act == 'add') {
                $('.type').text('Add');
            }else{
                $('.type').text('Subtract');
            }
        });

        let mobileElement = $('.mobile-code');
        $('select[name=country]').on('change',function(){
            mobileElement.text(`+${$('select[name=country] :selected').data('mobile_code')}`);
        });

        // Dynamic Account Prices Logic
        let existingPrices = @json($user->account_prices ?: (object)[]);
        let accountSelector = $('#account-selector');
        let pricesContainer = $('#account-prices-container');

        function renderPriceInputs() {
            let selectedOptions = accountSelector.find('option:selected');
            let html = '';
            
            // Retain values if they were just typed
            let currentValues = {};
            pricesContainer.find('input[type=number]').each(function() {
                let id = $(this).data('id');
                currentValues[id] = $(this).val();
            });

            selectedOptions.each(function() {
                let accountId = $(this).val();
                let accountName = $(this).data('name');
                let price = currentValues[accountId] || existingPrices[accountId] || 0;
                
                html += `
                    <div class="form-group mt-2 mb-2 p-2 border rounded">
                        <label class="d-block font-weight-bold" style="font-size: 12px;">Price for: ${accountName}</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ gs('cur_sym') }}</span>
                            <input type="number" step="any" min="0" class="form-control form-control-sm" name="account_prices[${accountId}]" data-id="${accountId}" value="${price}" placeholder="0.00" required>
                        </div>
                    </div>
                `;
            });
            pricesContainer.html(html);
        }

        accountSelector.on('change', renderPriceInputs);
        renderPriceInputs(); // Call on load

        // Password Reset Additions
        $('#generate-password-btn').on('click', function() {
            const chars = "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-+<>ABCDEFGHIJKLMNOP1234567890";
            let pass = "";
            for (let x = 0; x < 12; x++) {
                let i = Math.floor(Math.random() * chars.length);
                pass += chars.charAt(i);
            }
            $('#reset-password-field').val(pass);
            $('#reset-password-field').attr('type', 'text');
            $('#toggle-password-btn').find('i').removeClass('la-eye').addClass('la-eye-slash');
        });

        $('#toggle-password-btn').on('click', function() {
            let field = $('#reset-password-field');
            let icon = $(this).find('i');
            if (field.attr('type') === 'password') {
                field.attr('type', 'text');
                icon.removeClass('la-eye').addClass('la-eye-slash');
            } else {
                field.attr('type', 'password');
                icon.removeClass('la-eye-slash').addClass('la-eye');
            }
        });

        $('#copy-password-btn').on('click', function() {
            let pass = $('#reset-password-field').val();
            if(!pass) return;
            var tempInput = $("<input>");
            $("body").append(tempInput);
            tempInput.val(pass).select();
            document.execCommand("copy");
            tempInput.remove();
            
            let icon = $(this).find('i');
            icon.removeClass('la-copy').addClass('la-check');
            setTimeout(() => {
                icon.removeClass('la-check').addClass('la-copy');
            }, 1000);
        });

    })(jQuery);
</script>
@endpush
