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
                                    <th>@lang('Platform Name')</th>
                                    <th>@lang('Domain')</th>
                                    <th>@lang('URL')</th>
                                    <th>@lang('Accounts')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($socialsMedia as $socialMedia)
                                    <tr>
                                        <td><strong>{{ __($socialMedia->name) }}</strong></td>
                                        <td><code>{{ $socialMedia->domain }}</code></td>
                                        <td><a href="{{ $socialMedia->url }}" target="_blank">{{ $socialMedia->url }}</a></td>
                                        <td>
                                            <a class="badge badge--primary" href="{{ route('admin.account.listing.by.platform', $socialMedia->id) }}">
                                                {{ $socialMedia->account_listing_count }} @lang('Accounts')
                                            </a>
                                        </td>
                                        <td>@php echo $socialMedia->statusBadge; @endphp</td>
                                        <td>
                                            <div class="d-flex justify-content-end flex-wrap gap-1">
                                                <button class="btn btn-outline--primary editBtn cuModalBtn btn-sm"
                                                    data-modal_title="@lang('Update Platform')"
                                                    data-resource="{{ $socialMedia }}">
                                                    <i class="las la-pen"></i>@lang('Edit')
                                                </button>
                                                <a class="btn btn-sm btn-outline--info" href="{{ route('admin.account.listing.by.platform', $socialMedia->id) }}">
                                                    <i class="las la-key"></i> @lang('Manage Accounts')
                                                </a>
                                                <button class="btn btn-outline--success btn-sm loadBalanceBtn"
                                                    data-id="{{ $socialMedia->id }}"
                                                    data-name="{{ $socialMedia->name }}">
                                                    <i class="las la-balance-scale"></i>@lang('Load Balance')
                                                </button>
                                                @if ($socialMedia->status == Status::ENABLE)
                                                    <button class="btn btn-outline--warning btn-sm confirmationBtn"
                                                        data-question="@lang('Are you sure to disable this platform?')"
                                                        data-action="{{ route('admin.social.media.status', $socialMedia->id) }}">
                                                        <i class="las la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline--success btn-sm confirmationBtn"
                                                        data-question="@lang('Are you sure to enable this platform?')"
                                                        data-action="{{ route('admin.social.media.status', $socialMedia->id) }}">
                                                        <i class="las la-eye"></i>@lang('Enable')
                                                    </button>
                                                @endif
                                                <button class="btn btn-outline--danger btn-sm confirmationBtn"
                                                    data-question="@lang('Are you sure you want to delete this platform and ALL of its associated accounts?')"
                                                    data-action="{{ route('admin.social.media.delete', $socialMedia->id) }}">
                                                    <i class="las la-trash"></i>@lang('Delete')
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
                        </table>
                    </div>
                </div>
                @if ($socialsMedia->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($socialsMedia) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Load Balance Modal --}}
    <div class="modal fade" id="loadBalanceModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Automatic Load Balance') - <span id="loadBalancePlatformName"></span></h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="loadBalanceForm">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            @lang('Select how you want to auto load-balance active accounts for this platform among all active, non-banned users with a valid subscription:')
                        </p>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="mode" id="modeOverride" value="override_manual" checked>
                            <label class="form-check-label fw-bold" for="modeOverride">
                                @lang('1. Force Auto Load Balance (Override Manual Assignments)')
                            </label>
                            <small class="text-muted d-block ms-4">
                                @lang('Re-balances ALL eligible users evenly across active accounts for this platform. Any existing manual account assignments will be replaced.')
                            </small>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="mode" id="modeKeep" value="keep_manual">
                            <label class="form-check-label fw-bold" for="modeKeep">
                                @lang('2. Smart Load Balance (Keep Manual Assignments Intact)')
                            </label>
                            <small class="text-muted d-block ms-4">
                                @lang('Preserves existing manual account assignments for users who have them, and auto load-balances only remaining unassigned users.')
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Execute Load Balance')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add/Edit Platform Modal --}}
    <div class="modal fade" id="cuModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.social.media.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Platform Name') <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" type="text" placeholder="e.g. ChatGPT" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Cookie Domain') <span class="text-danger">*</span></label>
                            <input class="form-control" name="domain" type="text" placeholder=".chatgpt.com" required>
                            <small class="text-muted">@lang('Domain where cookies will be injected. Use leading dot for subdomains.')</small>
                        </div>
                        <div class="form-group">
                            <label>@lang('Platform URL') <span class="text-danger">*</span></label>
                            <input class="form-control" name="url" type="url" placeholder="https://chatgpt.com" required>
                            <small class="text-muted">@lang('Users will be redirected here when accessing this platform.')</small>
                        </div>
                        <div class="form-group">
                            <label>@lang('Instructions')</label>
                            <textarea class="form-control" name="instructions" rows="3" placeholder="@lang('e.g. Please use the extension to login.')"></textarea>
                            <small class="text-muted">@lang('Optional. These instructions will be displayed on the platform card for all users assigned to this platform.')</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Save Platform')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
    <button class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add New Platform')">
        <i class="las la-plus"></i>@lang('Add Platform')
    </button>
@endpush

@push('script')
<script>
    (function($){
        "use strict";
        $('.loadBalanceBtn').on('click', function () {
            var modal = $('#loadBalanceModal');
            var id = $(this).data('id');
            var name = $(this).data('name');
            modal.find('#loadBalancePlatformName').text(name);
            var actionUrl = "{{ route('admin.social.media.load.balance', ':id') }}".replace(':id', id);
            modal.find('#loadBalanceForm').attr('action', actionUrl);
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
