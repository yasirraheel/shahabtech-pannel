@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Repoter')</th>
                                    <th>@lang('Report') </th>
                                    <th> @lang('Date Time') </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.users.detail', @$report->user->id) }}"><span>@</span>{{ @$report->user->username }}</a>
                                           
                                        </td>
                                        <td>
                                            {{ strLimit($report->report,20) }}
                                            <span class="reportBtn" data-report='{{$report->report}}' data-bs-toggle="tooltip" data-bs-html="true" title="@lang('Details')">
                                                <i class="fas fa-bug"></i>
                                            </span>
                                        </td>
                                        <td>
                                            {{ showDateTime($report->created_at) }}
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
                @if ($reports->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($reports) }}
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div class="modal fade custom--modal" id="reportModal" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span>@lang('Report')</span>
                    </h5>
                    <button class="btn-close modal-icon" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="report-box"></p>
                </div>
            </div>
        </div>
    </div>


@endsection


@push('breadcrumb-plugins')
    <x-search-form />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.reportBtn').on('click', function() {
                var modal = $('#reportModal');
                modal.find('.report-box').html(($(this).data('report')));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush

@push('style')
<style>
    .reportBtn{
        cursor: pointer;
        margin-left: 10px;
    }
</style>
@endpush

