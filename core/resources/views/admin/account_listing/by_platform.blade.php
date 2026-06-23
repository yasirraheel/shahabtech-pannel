@extends('admin.layouts.app')
@section('panel')

    {{-- Platform Info Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                    <div>
                        <h5 class="mb-1">{{ $platform->name }}</h5>
                        <small class="text-muted">Domain: <code>{{ $platform->domain }}</code></small>
                        &nbsp;&nbsp;
                        <small class="text-muted">URL: <a href="{{ $platform->url }}" target="_blank">{{ $platform->url }}</a></small>
                    </div>
                    <div class="ms-auto">
                        <span class="badge badge--{{ $platform->status == Status::ENABLE ? 'success' : 'danger' }}">
                            {{ $platform->status == Status::ENABLE ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Account Title')</th>
                                    <th>@lang('Plan')</th>
                                    <th>@lang('Has Cookies')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountListings as $account)
                                    <tr>
                                        <td><strong>{{ $account->title }}</strong></td>
                                        <td>{{ @$account->plan->name ?? '—' }}</td>
                                        <td>
                                            @if($account->account_info)
                                                <span class="badge badge--success"><i class="las la-check"></i> Yes</span>
                                            @else
                                                <span class="badge badge--danger"><i class="las la-times"></i> No</span>
                                            @endif
                                        </td>
                                        <td>@php echo $account->statusBadge; @endphp</td>
                                        <td>
                                            <div class="d-flex justify-content-end flex-wrap gap-1">
                                                <button class="btn btn-outline--primary editBtn cuModalBtn btn-sm"
                                                    data-modal_title="@lang('Edit Account')"
                                                    data-resource="{{ $account }}">
                                                    <i class="las la-pen"></i>@lang('Edit')
                                                </button>
                                                @if($account->status == Status::LISTING_ACTIVE)
                                                    <button class="btn btn-outline--danger btn-sm confirmationBtn"
                                                        data-question="@lang('Disable this account?')"
                                                        data-action="{{ route('admin.account.listing.status', $account->id) }}">
                                                        <i class="las la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline--success btn-sm confirmationBtn"
                                                        data-question="@lang('Enable this account?')"
                                                        data-action="{{ route('admin.account.listing.status', $account->id) }}">
                                                        <i class="las la-eye"></i>@lang('Enable')
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">@lang('No accounts added yet for this platform.')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($accountListings->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($accountListings) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add / Edit Account Modal --}}
    <div class="modal fade" id="cuModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.account.listing.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="social_media_id" value="{{ $platform->id }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Account Title / Label') <span class="text-danger">*</span></label>
                            <input class="form-control" name="title" type="text" placeholder="e.g. ChatGPT Pro - Account 1" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Category') <span class="text-danger">*</span></label>
                            <select class="form-control" name="category_id" required>
                                <option value="">@lang('Select Category')</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Plan')</label>
                            <select class="form-control" name="plan_id">
                                <option value="">@lang('Select Plan')</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('URL') <span class="text-danger">*</span></label>
                            <input class="form-control" name="url" type="url" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Cookies (JSON Array)') <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="account_info" rows="6"
                                placeholder='[{"name":"session","value":"abc123","domain":".chatgpt.com","path":"/","httpOnly":true,"secure":true}]'
                                required></textarea>
                            <small class="text-muted">
                                @lang('Export cookies using the') <strong>Cookie-Editor</strong> @lang('browser extension → Export as JSON, then paste here.')
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Save Account')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.social.media.index') }}" class="btn btn-sm btn-outline--secondary">
        <i class="las la-arrow-left"></i> @lang('Back to Platforms')
    </a>
    <button class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add Account for {{ $platform->name }}')">
        <i class="las la-plus"></i>@lang('Add Account')
    </button>
@endpush
