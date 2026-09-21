@php
    $answeredTicketsCount = \App\Models\SupportTicket::where('user_id', auth()->id())->where('status', \App\Constants\Status::TICKET_ANSWER)->count();
@endphp

<div class="sidebar bg--dark">
    <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="{{ route('user.home') }}" class="sidebar__main-logo">
                <img src="{{ siteLogo() }}" alt="@lang('Logo')">
            </a>
        </div>
        <div class="sidebar__menu-wrapper" id="sidebar__menuWrapper">
            <ul class="sidebar__menu">

                {{-- Dashboard --}}
                <li class="sidebar-menu-item {{ menuActive('user.home') }}">
                    <a href="{{ route('user.home') }}" class="nav-link">
                        <i class="menu-icon las la-tachometer-alt"></i>
                        <span class="menu-title">@lang('Dashboard')</span>
                    </a>
                </li>

                {{-- Subscription Plans --}}
                <li class="sidebar-menu-item {{ menuActive('plans*') }}">
                    <a href="{{ route('plans') }}" class="nav-link">
                        <i class="menu-icon las la-crown"></i>
                        <span class="menu-title">@lang('Subscription Plans')</span>
                    </a>
                </li>

                {{-- Support Tickets --}}
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive(['ticket*'], 3) }}">
                        <i class="menu-icon las la-headset"></i>
                        <span class="menu-title">@lang('Support Tickets')</span>
                        @if($answeredTicketsCount > 0)
                            <span class="menu-badge menu-badge-level-one bg--info ms-auto">{{ $answeredTicketsCount }}</span>
                        @endif
                    </a>
                    <div class="sidebar-submenu {{ menuActive(['ticket*'], 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive('ticket.index') }}">
                                <a href="{{ route('ticket.index') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('My Tickets')</span>
                                    @if($answeredTicketsCount > 0)
                                        <span class="menu-badge bg--info ms-auto">{{ $answeredTicketsCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('ticket.open') }}">
                                <a href="{{ route('ticket.open') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Open New Ticket')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Account Details & Settings --}}
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="{{ menuActive(['user.profile.setting', 'user.change.password', 'user.general.profile'], 3) }}">
                        <i class="menu-icon las la-user-cog"></i>
                        <span class="menu-title">@lang('Account Settings')</span>
                    </a>
                    <div class="sidebar-submenu {{ menuActive(['user.profile.setting', 'user.change.password', 'user.general.profile'], 2) }}">
                        <ul>
                            <li class="sidebar-menu-item {{ menuActive(['user.profile.setting', 'user.general.profile']) }}">
                                <a href="{{ route('user.profile.setting') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Profile Details')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item {{ menuActive('user.change.password') }}">
                                <a href="{{ route('user.change.password') }}" class="nav-link">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">@lang('Change Password')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Logout --}}
                <li class="sidebar-menu-item">
                    <a href="{{ route('user.logout') }}" class="nav-link text-danger">
                        <i class="menu-icon las la-sign-out-alt text-danger"></i>
                        <span class="menu-title text-danger">@lang('Logout')</span>
                    </a>
                </li>

            </ul>
        </div>
        <div class="version-info text-center text-uppercase">
            <span class="text--primary">{{ __(gs('site_name')) }}</span>
            <span class="text--success">@lang('User Portal')</span>
        </div>
    </div>
</div>
