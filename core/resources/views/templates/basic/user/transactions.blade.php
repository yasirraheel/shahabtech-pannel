@extends($activeTemplate . 'layouts.master')
@section('content')
    <section class="py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="show-filter mb-3 text-end">
                        <button class="btn btn--base showFilterBtn btn-sm" type="button"><i class="las la-filter"></i> @lang('Filter')</button>
                    </div>
                    <div class="responsive-filter-card mb-4">
                        <div class="">
                            <form>
                                <div class="d-flex align-items-end flex-wrap gap-4">
                                    <div class="flex-grow-1">
                                        <label class="form--label">@lang('Transaction Number')</label>
                                        <input class="form--control" name="search" type="text" value="{{ request()->search }}">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form--label">@lang('Type')</label>
                                        <select class="form--control select2" name="trx_type" data-minimum-results-for-search="-1">
                                            <option value="">@lang('All')</option>
                                            <option value="+" @selected(request()->trx_type == '+')>@lang('Plus')</option>
                                            <option value="-" @selected(request()->trx_type == '-')>@lang('Minus')</option>
                                        </select>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="from--lebel">@lang('Remark')</label>
                                        <select class="form--control select2" name="remark" data-minimum-results-for-search="-1">
                                            <option value="">@lang('All')</option>
                                            @foreach ($remarks as $remark)
                                                <option value="{{ $remark->remark }}" @selected(request()->remark == $remark->remark)>{{ __(keyToTitle($remark->remark)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-grow-1 align-self-end">
                                        <button class="btn btn--base btn--filter w-100"><i class="las la-filter"></i> @lang('Filter')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card custom--card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table--responsive--lg table">
                                    <thead>
                                        <tr>
                                            <th>@lang('Trx')</th>
                                            <th>@lang('Transacted')</th>
                                            <th>@lang('Amount')</th>
                                            <th>@lang('Charge / Seller Fee')</th>
                                            <th>@lang('Post Balance')</th>
                                            <th>@lang('Detail')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transactions as $trx)
                                            <tr>
                                                <td>
                                                    <strong>{{ $trx->trx }}</strong>
                                                </td>

                                                <td>
                                                    {{ showDateTime($trx->created_at) }}<br>{{ diffForHumans($trx->created_at) }}
                                                </td>

                                                <td class="budget">
                                                    <span class="fw-bold @if ($trx->trx_type == '+') text--success @else text--danger @endif">
                                                        {{ $trx->trx_type }} {{ showAmount($trx->amount) }} {{ __(gs('cur_text')) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ showAmount($trx->charge) }} {{__(gs('cur_text')) }}
                                                </td>

                                                <td class="budget">
                                                    {{ showAmount($trx->post_balance) }} {{ __(gs('cur_text')) }}
                                                </td>

                                                <td>{{ __($trx->details) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-muted text-center" colspan="100%">
                                                    @include($activeTemplate . 'empty', ['message' => $emptyMessage])
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($transactions->hasPages())
                <div class="card-footer">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
