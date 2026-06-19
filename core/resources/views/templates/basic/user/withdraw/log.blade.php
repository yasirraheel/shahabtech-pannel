@extends($activeTemplate . 'layouts.master')
@section('content')
    <section class="py-120">
        <div class="container">
            <div class="row justify-content-center mt-2">
                <div class="col-lg-12">
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
                                <table class="table table--responsive--lg">
                                    <thead>
                                        <tr>
                                            <th>@lang('Gateway | Transaction')</th>
                                            <th class="text-center">@lang('Initiated')</th>
                                            <th class="text-center">@lang('Amount')</th>
                                            <th class="text-center">@lang('Conversion')</th>
                                            <th class="text-center">@lang('Status')</th>
                                            <th>@lang('Action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
    
                                    @forelse($withdraws as $withdraw)
                                        @php
                                            $details = [];
                                            foreach ($withdraw->withdraw_information as $key => $info) {
                                                $details[] = $info;
                                                if ($info->type == 'file') {
                                                    $details[$key]->value = route('user.download.attachment',encrypt(getFilePath('verify').'/'.$info->value));
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div>
                                                    <span class="fw-bold"><span class="text-primary"> {{ __(@$withdraw->method->name) }}</span></span>
                                                <br>
                                                <small>{{ $withdraw->trx }}</small>
                                                </div>
                                            </td>
                                            <td class="text-lg-center">
                                                <div>
                                                    {{ showDateTime($withdraw->created_at) }} <br>  {{ diffForHumans($withdraw->created_at) }}
                                                </div>
                                            </td>
                                            <td class="text-lg-center">
                                               <div>
                                                {{ showAmount($withdraw->amount) }} - <span class="text--danger" data-bs-toggle="tooltip" title="@lang('Processing Charge')">{{ showAmount($withdraw->charge)}} </span>
                                                <br>
                                                <strong data-bs-toggle="tooltip" title="@lang('Amount after charge')">
                                                {{ showAmount($withdraw->amount-$withdraw->charge) }}
                                                </strong>
                                               </div>
    
                                             </td>
                                             <td class="text-lg-center">
                                               <div>
                                                {{ showAmount(1) }} = {{ showAmount($withdraw->rate,currencyFormat:false) }} {{ __($withdraw->currency) }}
                                                <br>
                                                <strong>{{ showAmount($withdraw->final_amount,currencyFormat:false) }} {{ __($withdraw->currency) }}</strong>
                                               </div>
                                             </td>
                                             <td class="text-center">
                                                @php echo $withdraw->statusBadge @endphp
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn--base detailBtn"
                                                data-user_data="{{ json_encode($details) }}"
                                                @if ($withdraw->status == Status::PAYMENT_REJECT)
                                                data-admin_feedback="{{ $withdraw->admin_feedback }}"
                                                @endif
                                                >
                                                    <i class="la la-desktop"></i>
                                                </button>
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
                        @if ($withdraws->hasPages())
                            <div class="card-footer">
                                {{ $withdraws->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- APPROVE MODAL --}}
    <div class="modal custom--modal fade" id="detailModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button class="btn-close modal-icon" data-bs-dismiss="modal" type="button" aria-label="Close"> <i class="las la-times"></i></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData">

                    </ul>
                    <div class="feedback"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn--danger btn-sm" data-bs-dismiss="modal" type="button">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');
                var userData = $(this).data('user_data');
                var html = ``;
                userData.forEach(element => {
                    if (element.type != 'file') {
                        html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>${element.name}</span>
                            <span">${element.value}</span>
                        </li>`;
                    }
                });
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
