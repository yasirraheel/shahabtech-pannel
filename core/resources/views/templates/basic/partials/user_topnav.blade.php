@php
    $answeredTickets = \App\Models\SupportTicket::where('user_id', auth()->id())->where('status', \App\Constants\Status::TICKET_ANSWER)->latest()->take(5)->get();
    $answeredTicketsCount = $answeredTickets->count();
@endphp

<nav class="navbar-wrapper bg--dark d-flex flex-wrap">
    <div class="navbar__left">
        <button type="button" class="res-sidebar-open-btn me-3"><i class="las la-bars"></i></button>
        <div class="d-none d-md-flex align-items-center">
            <span class="text-white fw-bold">
                <i class="las la-user-circle text--primary me-1" style="font-size: 18px;"></i>
                @lang('Hi'), <span class="text--primary">{{ auth()->user()->fullname ?: auth()->user()->username }}</span>
            </span>
            @if(auth()->user()->is_trial)
                <span class="badge badge--warning ms-2">@lang('Trial')</span>
            @elseif(auth()->user()->plan)
                <span class="badge badge--success ms-2">{{ __(auth()->user()->plan->name) }}</span>
            @endif
        </div>
    </div>
    <div class="navbar__right">
        <ul class="navbar__action-list">

            {{-- Visit Website --}}
            <li>
                <button type="button" class="primary--layer" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Visit Website')">
                    <a href="{{ route('home') }}" target="_blank"><i class="las la-globe"></i></a>
                </button>
            </li>

            {{-- Support Tickets Bell --}}
            <li class="dropdown">
                <button type="button" class="primary--layer notification-bell" data-bs-toggle="dropdown" data-display="static"
                    aria-haspopup="true" aria-expanded="false">
                    <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Support Tickets')">
                        <i class="las la-bell @if($answeredTicketsCount > 0) icon-left-right @endif"></i>
                    </span>
                    @if($answeredTicketsCount > 0)
                        <span class="notification-count">{{ $answeredTicketsCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu--md p-0 border-0 box--shadow1 dropdown-menu-right">
                    <div class="dropdown-menu__header">
                        <span class="caption">@lang('Ticket Notifications')</span>
                        @if($answeredTicketsCount > 0)
                            <p>@lang('You have') {{ $answeredTicketsCount }} @lang('answered support ticket(s)')</p>
                        @endif
                    </div>
                    <div class="dropdown-menu__body @if($answeredTickets->isEmpty()) d-flex justify-content-center align-items-center @endif">
                        @forelse($answeredTickets as $ticket)
                            <a href="{{ route('ticket.view', $ticket->ticket) }}" class="dropdown-menu__item">
                                <div class="navbar-notifi">
                                    <div class="navbar-notifi__right">
                                        <h6 class="notifi__title">@lang('Ticket') #{{ $ticket->ticket }}: {{ strLimit($ticket->subject, 35) }}</h6>
                                        <span class="time"><i class="far fa-clock"></i> {{ diffForHumans($ticket->last_reply) }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="empty-notification text-center py-3">
                                <i class="las la-bell-slash" style="font-size: 32px; color: #888;"></i>
                                <p class="mt-2 text-muted" style="font-size: 13px;">@lang('No new notifications')</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="dropdown-menu__footer">
                        <a href="{{ route('ticket.index') }}" class="view-all-message">@lang('View All Tickets')</a>
                    </div>
                </div>
            </li>

            {{-- User Profile Dropdown --}}
            <li class="dropdown d-flex profile-dropdown">
                <button type="button" data-bs-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                    <span class="navbar-user">
                        <span class="navbar-user__thumb">
                            @if(auth()->user()->image)
                                <img src="{{ getImage(getFilePath('userProfile') . '/' . auth()->user()->image, getFileSize('userProfile')) }}" alt="user">
                            @else
                                <img src="{{ getImage($activeTemplateTrue . 'images/avatar.png') }}" alt="user">
                            @endif
                        </span>
                        <span class="navbar-user__info">
                            <span class="navbar-user__name">{{ auth()->user()->username }}</span>
                        </span>
                        <span class="icon"><i class="las la-chevron-circle-down"></i></span>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu--sm p-0 border-0 box--shadow1 dropdown-menu-right">
                    <a href="{{ route('user.profile.setting') }}" class="dropdown-menu__item d-flex align-items-center px-3 py-2">
                        <i class="dropdown-menu__icon las la-user-circle"></i>
                        <span class="dropdown-menu__caption">@lang('Profile Details')</span>
                    </a>

                    <a href="{{ route('user.change.password') }}" class="dropdown-menu__item d-flex align-items-center px-3 py-2">
                        <i class="dropdown-menu__icon las la-key"></i>
                        <span class="dropdown-menu__caption">@lang('Change Password')</span>
                    </a>

                    <a href="{{ route('user.logout') }}" class="dropdown-menu__item d-flex align-items-center px-3 py-2 text-danger">
                        <i class="dropdown-menu__icon las la-sign-out-alt text-danger"></i>
                        <span class="dropdown-menu__caption text-danger">@lang('Logout')</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>
