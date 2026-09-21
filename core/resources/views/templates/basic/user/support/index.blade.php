@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0">@lang('All Support Tickets')</h6>
                <a class="btn btn-sm btn--primary" href="{{ route('ticket.open') }}">
                    <i class="las la-plus"></i> @lang('New Ticket')
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Subject')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Priority')</th>
                                    <th>@lang('Last Reply')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supports as $support)
                                    <tr>
                                        <td>
                                            <a class="fw-bold text--primary" href="{{ route('ticket.view', $support->ticket) }}">
                                                [@lang('Ticket') #{{ $support->ticket }}] {{ __($support->subject) }}
                                            </a>
                                        </td>
                                        <td>
                                            @php echo $support->statusBadge; @endphp
                                        </td>
                                        <td>
                                            @if ($support->priority == Status::PRIORITY_LOW)
                                                <span class="badge badge--dark">@lang('Low')</span>
                                            @elseif($support->priority == Status::PRIORITY_MEDIUM)
                                                <span class="badge badge--warning">@lang('Medium')</span>
                                            @elseif($support->priority == Status::PRIORITY_HIGH)
                                                <span class="badge badge--danger">@lang('High')</span>
                                            @endif
                                        </td>
                                        <td>{{ diffForHumans($support->last_reply) }}</td>
                                        <td>
                                            <a class="btn btn--primary btn-sm" href="{{ route('ticket.view', $support->ticket) }}" title="@lang('View Details')">
                                                <i class="las la-desktop"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted py-4" colspan="100%">
                                            <i class="las la-headset" style="font-size: 32px;"></i>
                                            <p class="mt-2 mb-0">@lang('No support tickets found')</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($supports->hasPages())
                    <div class="card-footer py-4">
                        {{ $supports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
