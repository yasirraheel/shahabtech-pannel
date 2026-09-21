@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            {{-- Quick Action Buttons --}}
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="{{ route('admin.report.login.history') }}?search={{ $user->username }}" class="btn btn-sm btn-outline--primary flex-fill">
                    <i class="las la-list-alt"></i> @lang('Login History')
                </a>
                <a href="{{ route('admin.users.notification.log', $user->id) }}" class="btn btn-sm btn-outline--info flex-fill">
                    <i class="las la-bell"></i> @lang('Notifications')
                </a>
                @if($user->status == Status::USER_ACTIVE)
                    <button type="button" class="btn btn-sm btn-outline--warning flex-fill" data-bs-toggle="modal" data-bs-target="#userStatusModal">
                        <i class="las la-ban"></i> @lang('Ban User')
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-outline--success flex-fill" data-bs-toggle="modal" data-bs-target="#userStatusModal">
                        <i class="las la-undo"></i> @lang('Unban User')
                    </button>
                @endif
                <button type="button" class="btn btn-sm btn-outline--danger flex-fill" data-bs-toggle="modal" data-bs-target="#userLogoutModal">
                    <i class="las la-sign-out-alt"></i> @lang('Logout Remotely')
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg--primary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-user-edit me-1"></i> @lang('Edit User') — {{ $user->fullname ?: $user->username }}
                    </h5>
                    <div>
                        @if($user->status == Status::USER_ACTIVE)
                            <span class="badge badge--success">@lang('Active')</span>
                        @else
                            <span class="badge badge--danger">@lang('Banned')</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.users.update', [$user->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="account_ids_submitted" value="1">

                        {{-- Full Name --}}
                        <div class="form-group mb-4">
                            <label class="fw-bold text--dark mb-2 required">
                                <i class="las la-user text--primary"></i> @lang('Full Name')
                            </label>
                            <input class="form-control form-control-lg" type="text" name="name" id="userNameInput" value="{{ old('name', $user->fullname ?: ($user->firstname . ' ' . $user->lastname)) }}" required>
                        </div>

                        {{-- Email Address --}}
                        <div class="form-group mb-4">
                            <label class="fw-bold text--dark mb-2 required">
                                <i class="las la-envelope text--primary"></i> @lang('Email Address')
                            </label>
                            <input class="form-control form-control-lg" type="text" name="email" id="emailInput" value="{{ old('email', $user->email) }}" required>
                            <small class="text-muted mt-1 d-block">
                                <i class="las la-info-circle"></i> @lang('Username / Email address used for extension & web portal login.')
                            </small>
                        </div>

                        {{-- Password (Reset / Update) --}}
                        <div class="form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="fw-bold text--dark mb-0">
                                    <i class="las la-key text--primary"></i> @lang('Password') <span class="text-muted fw-normal">(@lang('leave blank to keep unchanged'))</span>
                                </label>
                                <a href="javascript:void(0)" class="text--primary fw-bold text-decoration-none" id="generatePasswordBtn" style="font-size: 13px;">
                                    <i class="las la-random"></i> @lang('Generate Random')
                                </a>
                            </div>
                            <div class="input-group input-group-lg">
                                <input class="form-control" type="password" name="password" id="passwordField" placeholder="@lang('Type new password or generate')">
                                <button type="button" class="btn btn--primary px-3 d-flex align-items-center justify-content-center" id="togglePassword" title="@lang('Toggle Visibility')" style="cursor:pointer; min-width: 50px;">
                                    <i class="las la-eye" style="font-size: 20px; color: #fff;"></i>
                                </button>
                                <button type="button" class="btn btn--dark px-3 d-flex align-items-center justify-content-center copy-btn" title="@lang('Copy Password')" style="cursor:pointer; min-width: 50px;">
                                    <i class="las la-copy" style="font-size: 20px; color: #fff;"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Assign Available Accounts --}}
                        <div class="form-group mb-4">
                            <label class="fw-bold text--dark mb-2">
                                <i class="las la-shield-alt text--primary"></i> @lang('Assign Available Accounts')
                            </label>
                            @php
                                $assignedIds = (array) ($user->account_ids ?? []);
                            @endphp
                            <select name="account_ids[]" class="form-control select2" multiple="multiple" id="account-selector" data-placeholder="@lang('Select Available Active Accounts')">
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(in_array($account->id, $assignedIds) || in_array((string)$account->id, $assignedIds))>
                                        {{ __(@$account->socialMedia->name) }} — {{ __($account->title) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-1 d-block">
                                @if($accounts->count() > 0)
                                    <span class="text--success fw-bold"><i class="las la-check-circle"></i> {{ $accounts->count() }} @lang('active platform accounts currently available.')</span>
                                @else
                                    <span class="text--warning"><i class="las la-exclamation-triangle"></i> @lang('No active accounts available right now.')</span>
                                @endif
                            </small>
                        </div>

                        {{-- User Privileges & Access Controls --}}
                        <input type="hidden" name="privileges_submitted" value="1">
                        <div class="form-group mb-4">
                            <label class="fw-bold text--dark mb-2">
                                <i class="las la-user-shield text--primary"></i> @lang('User Privileges & Permissions')
                            </label>
                            <div class="row g-3">
                                {{-- Tester User Toggle --}}
                                <div class="col-md-6">
                                    <div class="card border p-3 h-100" style="background: #f8fafc; border-radius: 8px;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text--dark">
                                                <i class="las la-vial text-warning me-1"></i> @lang('Tester User Mode')
                                            </span>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" name="is_tester" value="1" id="isTesterSwitch" @checked(old('is_tester', $user->is_tester)) style="cursor: pointer; width: 42px; height: 22px;">
                                            </div>
                                        </div>
                                        <small class="text-muted" style="font-size: 12.5px; line-height: 1.4;">
                                            @lang('Enables Tester Mode. User sees developer controls and gets access to all active accounts.')
                                        </small>
                                    </div>
                                </div>

                                {{-- Cookie Access (Exclusive) Toggle --}}
                                <div class="col-md-6">
                                    <div class="card border p-3 h-100" style="background: #f8fafc; border-radius: 8px;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text--dark">
                                                <i class="las la-cookie-bite text-info me-1"></i> @lang('Cookie Access (Copy Cookie)')
                                            </span>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" name="is_exclusive" value="1" id="isExclusiveSwitch" @checked(old('is_exclusive', $user->is_exclusive)) style="cursor: pointer; width: 42px; height: 22px;">
                                            </div>
                                        </div>
                                        <small class="text-muted" style="font-size: 12.5px; line-height: 1.4;">
                                            @lang('Allows user to view & copy platform cookies in JSON format via the "Copy Cookie" button on their dashboard.')
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="mt-4 pt-2">
                            <button type="submit" class="btn btn--primary btn-lg w-100 h-45 shadow-sm fw-bold">
                                <i class="las la-save me-1"></i> @lang('Save Changes')
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Ban/Unban Modal --}}
    <div id="userStatusModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($user->status == Status::USER_ACTIVE)
                            <span>@lang('Ban User')</span>
                        @else
                            <span>@lang('Unban User')</span>
                        @endif
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.users.status', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($user->status == Status::USER_ACTIVE)
                            <p class="mb-3">@lang('If you ban this user, they will not be able to access the platform or extension.')</p>
                            <div class="form-group">
                                <label class="fw-bold required">@lang('Reason for Ban')</label>
                                <textarea class="form-control" name="reason" rows="3" required placeholder="@lang('State reason for banning...')"></textarea>
                            </div>
                        @else
                            <p class="mb-2">@lang('Are you sure you want to unban this user?')</p>
                            <p class="text-muted">@lang('Ban reason was:') <strong>{{ $user->ban_reason }}</strong></p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--primary">
                            @if($user->status == Status::USER_ACTIVE)
                                @lang('Confirm Ban')
                            @else
                                @lang('Confirm Unban')
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Remote Logout Modal --}}
    <div id="userLogoutModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Logout User Remotely')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.users.logout', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-2">@lang('Are you sure you want to remotely terminate all active sessions for this user?')</p>
                        <small class="text-muted">@lang('This will invalidate their active session and remember-token immediately.')</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--danger">@lang('Logout User')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.login', $user->id) }}" target="_blank" class="btn btn-sm btn-outline--primary">
        <i class="las la-sign-in-alt"></i> @lang('Login as User')
    </a>
    <a href="{{ route('admin.users.all') }}" class="btn btn-sm btn-outline--dark ms-2">
        <i class="las la-arrow-left"></i> @lang('Back to Users')
    </a>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";

        function generateRandomPassword(length = 10) {
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            let pwd = "";
            for (let i = 0; i < length; i++) {
                pwd += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return pwd;
        }

        // Generate Password Button
        $('#generatePasswordBtn').on('click', function(e) {
            e.preventDefault();
            let pwd = generateRandomPassword(10);
            $('#passwordField').val(pwd).attr('type', 'text');
            $('#togglePassword').html('<i class="las la-eye-slash" style="font-size: 20px; color: #fff;"></i>');
            notify('success', 'New random password generated!');
        });

        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            let pwd = $('#passwordField');
            if (pwd.attr('type') === 'password') {
                pwd.attr('type', 'text');
                $(this).html('<i class="las la-eye-slash" style="font-size: 20px; color: #fff;"></i>');
            } else {
                pwd.attr('type', 'password');
                $(this).html('<i class="las la-eye" style="font-size: 20px; color: #fff;"></i>');
            }
        });

        // Copy Password
        $('.copy-btn').on('click', function () {
            let copyText = document.getElementById("passwordField");
            if (!copyText.value) {
                notify('warning', 'Password field is empty!');
                return;
            }
            let originalType = copyText.type;
            copyText.type = "text";
            copyText.select();
            document.execCommand("copy");
            copyText.type = originalType;
            notify('success', 'Password copied to clipboard!');
        });

    })(jQuery);
</script>
@endpush
