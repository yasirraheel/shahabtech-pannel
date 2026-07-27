@extends('admin.layouts.master')
@section('content')
@php
    $sidenav = file_get_contents(resource_path('views/admin/partials/sidenav.json'));
@endphp
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">
        @include('admin.partials.sidenav')
        @include('admin.partials.topnav')

        <div class="container-fluid px-3 px-sm-0">
            <div class="body-wrapper">
                <div class="bodywrapper__inner">

                    @php
                        $pendingTicketsCount = \App\Models\SupportTicket::whereIn('status', [\App\Constants\Status::TICKET_OPEN, \App\Constants\Status::TICKET_REPLY])->count();
                    @endphp
                    @if($pendingTicketsCount > 0 && !request()->routeIs('admin.ticket.*'))
                        <div class="mb-3">
                            <div class="alert alert--warning d-flex align-items-center justify-content-between mb-0" style="background-color: rgba(255, 193, 7, 0.15); border: 1px solid #ffc107; color: #856404; padding: 10px 18px; border-radius: 6px;">
                                <div class="d-flex align-items-center">
                                    <i class="las la-headset me-2" style="font-size: 22px; color: #ff9800;"></i>
                                    <span><strong>@lang('Support Ticket Alert'):</strong> @lang('You have') <strong>{{ $pendingTicketsCount }}</strong> @lang('pending support ticket(s) waiting for response!')</span>
                                </div>
                                <a href="{{ route('admin.ticket.pending') }}" class="btn btn-sm btn--warning text-dark font-weight-bold" style="padding: 4px 12px; font-size: 12px;">
                                    <i class="las la-external-link-alt"></i> @lang('View Pending Tickets')
                                </a>
                            </div>
                        </div>
                    @endif

                    @stack('topBar')
                    @include('admin.partials.breadcrumb')

                    @yield('panel')

                </div><!-- bodywrapper__inner end -->
            </div><!-- body-wrapper end -->
        </div>
    </div>
@endsection
