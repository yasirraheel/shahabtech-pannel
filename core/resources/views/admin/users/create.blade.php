@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-12">
            <div class="card mt-30">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">@lang('Create New User')</h5>
                    <button type="button" class="btn btn-sm btn-outline--primary" id="generateUserBtn"><i class="las la-magic"></i> @lang('Quick Generate User')</button>
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
                                    <div class="input-group">
                                        <input class="form-control" type="password" name="password" id="passwordField" required>
                                        <button type="button" class="input-group-text" id="togglePassword"><i class="las la-eye"></i></button>
                                        <button type="button" class="input-group-text copy-btn"><i class="las la-copy"></i></button>
                                    </div>
                                    <small><a href="javascript:void(0)" id="generatePasswordBtn">@lang('Generate Random Password')</a></small>
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
                                        <input type="number" name="mobile" value="{{ old('mobile') }}" id="mobile" class="form-control checkUser">
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
                                    <label>@lang('Assign Specific Accounts')</label>
                                    <select name="account_ids[]" class="form-control select2" multiple="multiple">
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ __(@$account->socialMedia->name) }} - {{ __($account->title) }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">@lang('Assign specific accounts if you do not want to give them a full plan. You can select multiple.')</small>
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

        // Auto-fill email based on username
        $('input[name="username"]').on('input', function() {
            let val = $(this).val();
            if(val) {
                $('input[name="email"]').val(val.toLowerCase() + '@topdealsplus.com');
            } else {
                $('input[name="email"]').val('');
            }
        });

        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            let pwd = $('#passwordField');
            if(pwd.attr('type') === 'password') {
                pwd.attr('type', 'text');
                $(this).html('<i class="las la-eye-slash"></i>');
            } else {
                pwd.attr('type', 'password');
                $(this).html('<i class="las la-eye"></i>');
            }
        });

        // Copy Password
        $('.copy-btn').on('click', function () {
            let copyText = document.getElementById("passwordField");
            if(copyText.type === "password") {
                copyText.type = "text";
                copyText.select();
                document.execCommand("copy");
                copyText.type = "password";
            } else {
                copyText.select();
                document.execCommand("copy");
            }
            notify('success', 'Password Copied!');
        });

        function generateRandomString(length) {
            let chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let str = "";
            for (let i = 0; i < length; i++) {
                str += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return str;
        }
        
        function generateRandomNumberString(length) {
            let chars = "0123456789";
            let str = "";
            for (let i = 0; i < length; i++) {
                str += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return str;
        }

        // Generate Random Password
        $('#generatePasswordBtn').on('click', function() {
            $('#passwordField').val(generateRandomString(12));
            $('#passwordField').attr('type', 'text');
            $('#togglePassword').html('<i class="las la-eye-slash"></i>');
        });

        // Quick Generate User
        $('#generateUserBtn').on('click', function() {
            let firstNames = ["John", "Emma", "Michael", "Sarah", "David", "Jessica", "James", "Emily", "Robert", "Olivia", "William", "Sophia", "Daniel", "Isabella"];
            let lastNames = ["Smith", "Johnson", "Williams", "Brown", "Jones", "Garcia", "Miller", "Davis", "Rodriguez", "Martinez", "Hernandez", "Lopez"];
            
            let fName = firstNames[Math.floor(Math.random() * firstNames.length)];
            let lName = lastNames[Math.floor(Math.random() * lastNames.length)];
            let rNum = Math.floor(Math.random() * 9000) + 1000;
            
            $('input[name="firstname"]').val(fName);
            $('input[name="lastname"]').val(lName);
            
            let uName = fName.toLowerCase() + '_' + rNum;
            $('input[name="username"]').val(uName).trigger('input'); // This triggers the email auto-fill
            
            $('#generatePasswordBtn').click();
            
            // Random unique mobile number (10 digits)
            $('input[name="mobile"]').val(generateRandomNumberString(10));
            
            // Randomly select a country
            let options = $('#country option');
            let randomOption = options[Math.floor(Math.random() * options.length)];
            $('#country').val(randomOption.value).trigger('change');
            
            notify('success', 'Random user data generated successfully!');
        });
        
    })(jQuery);
</script>
@endpush
