@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Users')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plans as $plan)
                                    <tr>
                                        <td>{{ __($plan->name) }}</td>
                                        <td>{{ showAmount($plan->price) }}</td>
                                        <td>{{ $plan->users_count }}</td>
                                        <td>@php echo $plan->statusBadge; @endphp</td>
                                        <td>
                                            <div class="d-flex justify-content-end flex-wrap gap-1">
                                                <button class="btn btn-outline--primary editBtn cuModalBtn btn-sm"
                                                    data-modal_title="@lang('Update Plan')"
                                                    data-resource="{{ $plan }}">
                                                    <i class="las la-pen"></i>@lang('Edit')
                                                </button>
                                                @if($plan->status == Status::ENABLE)
                                                    <button class="btn btn-outline--danger btn-sm confirmationBtn"
                                                        data-question="@lang('Are you sure to disable this plan?')"
                                                        data-action="{{ route('admin.plan.status', $plan->id) }}">
                                                        <i class="las la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline--success btn-sm confirmationBtn"
                                                        data-question="@lang('Are you sure to enable this plan?')"
                                                        data-action="{{ route('admin.plan.status', $plan->id) }}">
                                                        <i class="las la-eye"></i>@lang('Enable')
                                                    </button>
                                                @endif
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
                @if($plans->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($plans) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="cuModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.plan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Plan Name')</label>
                            <input class="form-control" name="name" type="text" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Price')</label>
                            <input class="form-control" name="price" type="number" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Features')</label>
                            <textarea class="form-control" name="features" rows="5" placeholder="Enter features, one per line"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add Plan')">
        <i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";
        $('.editBtn').on('click', function () {
            var modal = $('#cuModal');
            var resource = $(this).data('resource');
            if(resource && resource.features) {
                modal.find('[name=features]').val(resource.features.join('\n'));
            } else {
                modal.find('[name=features]').val('');
            }
        });
        $('.cuModalBtn').not('.editBtn').on('click', function () {
            $('#cuModal').find('[name=features]').val('');
        });
    })(jQuery);
</script>
@endpush
