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
                                                @if ($socialMedia->status == Status::ENABLE)
                                                    <button class="btn btn-outline--danger btn-sm confirmationBtn"
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
