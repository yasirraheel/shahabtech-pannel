@extends($activeTemplate . 'layouts.master')
@section('content')
    <section class="py-120">
        <div class="container">

            <div class="show-filter mb-3 text-end">
                <button class="btn btn--base showFilterBtn btn-sm" type="button"><i class="las la-filter"></i> @lang('Filter')</button>
            </div>
            <div class="responsive-filter-card mb-4">
                <form class="listing-search-form">
                    <div class="d-flex align-items-end justify-content-end flex-wrap gap-4">
                        <div>
                            <input class="form-control form--control" name="search" type="text" value="{{ request()->search }}" placeholder="@lang('Search by transactions')">
                        </div>
                        <button class="btn btn--base btn--filter"><i class="fas fa-search"></i> @lang('Search')</button>

                    </div>
                </form>
            </div>

            <div class="card custom--card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table--responsive--lg table">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway | Transaction')</th>
                                    <th class="text-center">@lang('Initiated')</th>
                                    <th class="text-center">@lang('Amount')</th>
                                    <th class="text-center">@lang('Conversion')</th>
                                    <th class="text-center">@lang('Status')</th>
                                    <th>@lang('Details')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="fw-bold">
                                                    <span class="text-primary">
                                                        @if($deposit->method_code < 5000)
                                                            {{ __(@$deposit->gateway->name) }}
                                                        @else
                                                            @lang('Google Pay')
                                                        @endif
                                                    </span>
                                                </span>
                                                <br>
                                                <small> {{ $deposit->trx }} </small>
                                            </div>
                                       </td>

                                       <td class="text-lg-center">
                                           <div>
                                            {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                           </div>
                                       </td>
                                       <td class="text-lg-center">
                                          <div>
                                            {{ showAmount($deposit->amount ) }} + <span class="text--danger" data-bs-toggle="tooltip" title="@lang('Processing Charge')">{{ showAmount($deposit->charge)}} </span>
                                           <br>
                                           <strong data-bs-toggle="tooltip" title="@lang('Amount with charge')">
                                           {{ showAmount($deposit->amount+$deposit->charge) }}
                                           </strong>
                                          </div>
                                       </td>
                                       <td class="text-lg-center">
                                            <div>
                                                {{ showAmount(1) }}  =  {{ showAmount($deposit->rate,currencyFormat:false) }} {{__($deposit->method_currency)}}
                                                <br>
                                                <strong>{{ showAmount($deposit->final_amount,currencyFormat:false) }} {{__($deposit->method_currency)}}</strong>
                                            </div>
                                       </td>
                                       <td class="text-center">
                                           @php echo $deposit->statusBadge @endphp
                                       </td>
                                        @php
                                            $details = [];
                                            if($deposit->method_code >= 1000 && $deposit->method_code <= 5000){
                                                foreach (@$deposit->detail ?? [] as $key => $info) {
                                                    $details[] = $info;
                                                    if ($info->type == 'file') {
                                                        $details[$key]->value = route('user.download.attachment',encrypt(getFilePath('verify').'/'.$info->value));
                                                    }
                                                }
                                            }
                                        @endphp

                                        <td>
                                            @if($deposit->method_code >= 1000 && $deposit->method_code <= 5000)
                                            <a href="javascript:void(0)" class="btn btn--base btn-sm detailBtn" data-info="{{ json_encode($details) }}"
                                                @if ($deposit->status == Status::PAYMENT_REJECT)
                                                data-admin_feedback="{{ $deposit->admin_feedback }}"
                                                @endif
                                                >
                                                <i class="fas fa-desktop"></i>
                                            </a>
                                            @else
                                            <button type="button" class="btn btn--success btn-sm" data-bs-toggle="tooltip" title="@lang('Automatically processed')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            @if ($deposits->hasPages())
                <div class="card-footer">
                    {{ $deposits->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- APPROVE MODAL --}}
    <div class="modal fade custom--modal" id="detailModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <span class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData mb-2">
                    </ul>
                    <div class="feedback"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-dark btn-sm" data-bs-dismiss="modal" type="button">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
    </section>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');

                var userData = $(this).data('info');
                var html = '';
                if (userData) {
                    userData.forEach(element => {
                        if (element.type != 'file') {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${element.name}</span>
                                <span">${element.value}</span>
                            </li>`;
                        }
                    });
                }

                modal.find('.userData').html(html);

                if ($(this).data('admin_feedback') != undefined) {
                    var adminFeedback = `
                        <div class="my-3">
                            <strong>@lang('Admin Feedback')</strong>
                            <p>${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                modal.find('.feedback').html(adminFeedback);


                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
