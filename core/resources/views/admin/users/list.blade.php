@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Email-Mobile')</th>
                                <th>@lang('Country')</th>
                                <th>@lang('Joined At')</th>
                                <th>@lang('Expiry')</th>
                                <th>@lang('Balance')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{$user->fullname}}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.users.detail', $user->id) }}"><span>@</span>{{ $user->username }}</a>
                                    </span>
                                    @if($user->last_seen)
                                        <div class="mt-1">
                                            @if(\Carbon\Carbon::parse($user->last_seen)->diffInMinutes(now()) <= 3)
                                                <span class="badge badge--success" style="font-size: 10px; padding: 2px 6px;">Online</span>
                                            @else
                                                <span class="badge badge--secondary" style="font-size: 10px; padding: 2px 6px;">Offline</span>
                                            @endif
                                            <span class="text-muted d-block" style="font-size: 11px; margin-top: 2px;">
                                                Last seen: {{ \Carbon\Carbon::parse($user->last_seen)->diffForHumans() }}
                                            </span>
                                            @if($user->last_seen_ip)
                                            <span class="text-muted d-block" style="font-size: 11px;">
                                                Active IP: <a href="{{route('admin.report.login.ipHistory',[$user->last_seen_ip])}}">{{ $user->last_seen_ip }}</a>
                                            </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>


                                <td>
                                    {{ $user->email }}<br>{{ $user->mobileNumber }}
                                </td>
                                <td>
                                    <span class="fw-bold" title="{{ @$user->country_name }}">{{ $user->country_code }}</span>
                                </td>



                                <td>
                                    {{ showDateTime($user->created_at) }} <br> {{ diffForHumans($user->created_at) }}
                                </td>

                                <td>
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
                                        <br>
                                        <span class="small text-muted" style="font-size: 11px;">{{ showDateTime($expiry, 'd M Y') }}</span>
                                    @else
                                        <span class="badge badge--dark" style="font-size: 10px;">@lang('N/A')</span>
                                    @endif
                                </td>


                                <td>
                                    <span class="fw-bold">

                                    {{ showAmount($user->balance) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-desktop"></i> @lang('Details')
                                        </a>
                                        @if (request()->routeIs('admin.users.kyc.pending'))
                                        <a href="{{ route('admin.users.kyc.details', $user->id) }}" target="_blank" class="btn btn-sm btn-outline--dark">
                                            <i class="las la-user-check"></i>@lang('KYC Data')
                                        </a>
                                        @endif
                                        <button class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.users.delete', $user->id) }}" data-question="@lang('Are you sure you want to delete this user?')">
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
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($users->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($users) }}
                </div>
                @endif
            </div>
        </div>


        <x-confirmation-modal />
    </div>
@endsection



@push('breadcrumb-plugins')
    <x-search-form placeholder="Username / Email" />
    <a href="{{ route('admin.users.create') }}" class="btn btn-outline--primary">
        <i class="las la-plus"></i>@lang('Add New')
    </a>
@endpush
