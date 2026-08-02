@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table--light style--two mb-0" style="table-layout: fixed; width: 100%;">
                            <thead>
                            <tr>
                                <th style="width: 28%;">@lang('User')</th>
                                <th style="width: 22%;">@lang('Email-Mobile')</th>
                                <th style="width: 6%; text-align: center;">@lang('Country')</th>
                                <th style="width: 15%;">@lang('Joined At')</th>
                                <th style="width: 12%;">@lang('Expiry')</th>
                                <th style="width: 8%;">@lang('Balance')</th>
                                <th style="width: 9%; text-align: right;">@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td style="white-space: normal; word-break: break-word;">
                                    <span class="fw-bold text--dark">{{$user->fullname}}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.users.detail', $user->id) }}"><span>@</span>{{ $user->username }}</a>
                                    </span>
                                    @if($user->last_seen)
                                        <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                            @if(\Carbon\Carbon::parse($user->last_seen)->diffInMinutes(now()) <= 3)
                                                <span class="badge badge--success" style="font-size: 9px; padding: 2px 5px;">Online</span>
                                            @else
                                                <span class="badge badge--secondary" style="font-size: 9px; padding: 2px 5px;">Offline</span>
                                            @endif
                                            <span class="badge badge--dark" style="font-size: 9px; padding: 2px 5px;" title="Last Seen">
                                                <i class="las la-clock"></i> {{ \Carbon\Carbon::parse($user->last_seen)->diffForHumans() }}
                                            </span>
                                            <span class="badge badge--info" style="font-size: 9px; padding: 2px 5px;" title="Total Online Time">
                                                <i class="las la-stopwatch"></i> {{ $user->onlineTimeFormatted() }}
                                            </span>
                                        </div>
                                        @if($user->last_seen_ip)
                                            <div class="text-muted small mt-1" style="font-size: 10px; word-break: break-all;">
                                                IP: <a href="{{route('admin.report.login.ipHistory',[$user->last_seen_ip])}}">{{ $user->last_seen_ip }}</a>
                                            </div>
                                        @endif
                                        @php
                                            $assignedAccountsList = $user->assignedAccountListings();
                                        @endphp
                                        @if($assignedAccountsList->isNotEmpty())
                                            <div class="mt-1">
                                                <span class="text-muted d-block" style="font-size: 9px; margin-bottom: 1px;">
                                                    <i class="las la-layer-group"></i> <strong>Assigned Accounts:</strong>
                                                </span>
                                                @foreach($assignedAccountsList as $accItem)
                                                    <span class="badge badge--primary d-inline-block mb-1" style="font-size: 9px; padding: 2px 5px;">
                                                        {{ __(@$accItem->socialMedia->name) }} - {{ __($accItem->title) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <td style="white-space: normal; word-break: break-all; font-size: 12px;">
                                    <span class="d-block text--dark fw-semibold">{{ $user->email }}</span>
                                    <span class="text-muted small">{{ $user->mobileNumber }}</span>
                                </td>

                                <td style="text-align: center;">
                                    <span class="fw-bold badge badge--light border text-dark" title="{{ @$user->country_name }}">{{ $user->country_code }}</span>
                                </td>

                                <td style="white-space: normal; font-size: 11px;">
                                    <span class="d-block fw-semibold">{{ showDateTime($user->created_at, 'd M Y, h:i A') }}</span>
                                    <span class="text-muted small">{{ diffForHumans($user->created_at) }}</span>
                                </td>

                                <td style="white-space: normal;">
                                    @php
                                        $expiry = $user->expires_at;
                                        $isExpired = $expiry && $expiry->isPast();
                                        $daysRemaining = $expiry ? \Carbon\Carbon::now()->startOfDay()->diffInDays($expiry->copy()->startOfDay(), false) : null;
                                    @endphp
                                    @if($expiry)
                                        @if($isExpired)
                                            <span class="badge badge--danger" style="font-size: 10px;">@lang('Expired')</span>
                                        @else
                                            <span class="badge badge--success" style="font-size: 10px;">{{ ceil($daysRemaining) }} @lang('Days')</span>
                                        @endif
                                        <div class="small text-muted mt-1" style="font-size: 10px;">{{ showDateTime($expiry, 'd M Y') }}</div>
                                    @else
                                        <span class="badge badge--dark" style="font-size: 10px;">@lang('N/A')</span>
                                    @endif
                                </td>

                                <td style="white-space: nowrap; font-size: 12px;">
                                    <span class="fw-bold text--dark">
                                        {{ showAmount($user->balance) }}
                                    </span>
                                </td>

                                <td style="text-align: right; white-space: nowrap;">
                                    <div class="d-flex flex-column gap-1 align-items-end">
                                        <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn-xs btn-outline--primary py-1 px-2" style="font-size: 11px;">
                                            <i class="las la-desktop"></i> @lang('Details')
                                        </a>
                                        @if (request()->routeIs('admin.users.kyc.pending'))
                                        <a href="{{ route('admin.users.kyc.details', $user->id) }}" target="_blank" class="btn btn-xs btn-outline--dark py-1 px-2" style="font-size: 11px;">
                                            <i class="las la-user-check"></i>@lang('KYC')
                                        </a>
                                        @endif
                                        <button class="btn btn-xs btn-outline--danger confirmationBtn py-1 px-2" style="font-size: 11px;" data-action="{{ route('admin.users.delete', $user->id) }}" data-question="@lang('Are you sure you want to delete this user?')">
                                            <i class="las la-trash"></i> @lang('Delete')
                                        </button>
                                    </div>
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
                @if ($users->hasPages())
                    <div class="card-footer py-3">
                        {{ paginateLinks($users) }}
                    </div>
                @endif
            </div>
        </div>
        <x-confirmation-modal />
    </div>
@endsection


@push('breadcrumb-plugins')
    @if(request()->account_id)
        <a href="{{ route('admin.users.all') }}" class="btn btn-outline--danger">
            <i class="las la-times"></i> @lang('Clear Filter')
        </a>
    @endif
    <a href="{{ request()->fullUrlWithQuery(['sort' => request()->sort == 'last_seen' ? '' : 'last_seen']) }}" class="btn {{ request()->sort == 'last_seen' ? 'btn--primary' : 'btn-outline--primary' }}">
        <i class="las la-sort-amount-down"></i> @lang('Sort by Last Seen')
    </a>
    <x-search-form placeholder="Username / Email" />
    <a href="{{ route('admin.users.create') }}" class="btn btn-outline--primary">
        <i class="las la-plus"></i>@lang('Add New')
    </a>
@endpush
