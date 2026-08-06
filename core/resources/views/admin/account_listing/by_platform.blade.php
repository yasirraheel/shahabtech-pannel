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
                                    <th>@lang('Has Cookies')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountListings as $account)
                                    <tr>
                                        <td>
                                            <strong class="d-block mb-1">{{ $account->title }}</strong>
                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                <a href="{{ route('admin.users.all') }}?account_id={{ $account->id }}" class="badge badge--info" style="font-size: 11px; padding: 4px 8px; text-decoration: none;" title="@lang('Click to view assigned users')">
                                                    <i class="las la-users"></i> {{ $account->assignedUsersCount() }} @lang('Users')
                                                </a>
                                                @php echo $account->cookieStatusBadge; @endphp
                                            </div>
                                        </td>
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
                                                <a href="{{ route('admin.account.listing.check.cookie', $account->id) }}" class="btn btn-outline--dark btn-sm" title="@lang('Check Cookie Health Now')">
                                                    <i class="las la-cookie"></i>@lang('Check Cookie')
                                                </a>
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
                                                <button class="btn btn-outline--success btn-sm confirmationBtn" data-question="@lang('Are you sure you want to extend cookie expiry by 30 days?')" data-action="{{ route('admin.account.listing.modify.expiry', ['id' => $account->id, 'action' => 'extend']) }}">
                                                    <i class="las la-calendar-plus"></i>@lang('+30 Days')
                                                </button>
                                                <button class="btn btn-outline--warning btn-sm confirmationBtn" data-question="@lang('Are you sure you want to decrease cookie expiry by 30 days?')" data-action="{{ route('admin.account.listing.modify.expiry', ['id' => $account->id, 'action' => 'decrease']) }}">
                                                    <i class="las la-calendar-minus"></i>@lang('-30 Days')
                                                </button>
                                                <button class="btn btn-outline--info btn-sm duplicateBtn" data-id="{{ $account->id }}" data-title="{{ $account->title }} - Copy">
                                                    <i class="las la-copy"></i>@lang('Duplicate')
                                                </button>
                                                <button class="btn btn-outline--danger btn-sm confirmationBtn" data-question="@lang('Are you sure you want to delete this account?')" data-action="{{ route('admin.account.listing.delete', $account->id) }}">
                                                    <i class="las la-trash"></i>@lang('Delete')
                                                </button>
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

    {{-- Duplicate Account Modal --}}
    <div class="modal fade" id="duplicateModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Duplicate Account')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="duplicateForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('New Account Title / Name') <span class="text-danger">*</span></label>
                            <input class="form-control" name="title" type="text" id="duplicateTitle" required>
                            <small class="text-muted">@lang('All cookies, platform details, plan, and instructions will be copied automatically.')</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Duplicate Account')</button>
                    </div>
                </form>
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
                            <label>@lang('Account Title / Identifier') <span class="text-danger">*</span></label>
                            <input class="form-control" name="title" type="text" placeholder="@lang('e.g. Account #1')" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Category') <span class="text-danger">*</span></label>
                            <select class="form-control" name="category_id" required>
                                <option value="">@lang('Select Category')</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Assign to Plan')</label>
                            <select class="form-control" name="plan_id">
                                <option value="0">@lang('All Active Plans (Available to everyone)')</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">@lang('Select a specific plan, or leave as All Active Plans to make available to all users.')</small>
                        </div>
                        <div class="form-group">
                            <label>@lang('Platform URL') <span class="text-danger">*</span></label>
                            <input class="form-control" name="url" type="url" value="{{ $platform->url }}" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Cookies (JSON format)') <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="account_info" rows="6" placeholder='[{"name":"session","value":"xyz"...}]' required></textarea>
                            <small class="text-muted">
                                @lang('Paste exported JSON cookies array from your browser extension (e.g. Cookie-Editor).')
                            </small>
                        </div>
                        <div class="form-group">
                            <label>@lang('Instructions')</label>
                            <textarea class="form-control" name="instructions" rows="3" placeholder="@lang('e.g. Please use the extension to login.')"></textarea>
                            <small class="text-muted">@lang('Optional. These instructions will be displayed on the platform card for subscribed users.')</small>
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
    <div class="dropdown d-inline-block">
        <button class="btn btn-sm btn-outline--secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="las la-sort-amount-down me-1"></i>
            @if(session('admin_account_sort', 'last_updated') === 'last_updated')
                @lang('Sort: Update Time')
            @elseif(session('admin_account_sort') === 'cookie_health')
                @lang('Sort: Cookie Health')
            @elseif(session('admin_account_sort') === 'title_asc')
                @lang('Sort: Title (A-Z)')
            @else
                @lang('Sort: Newest')
            @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
            <li>
                <a class="dropdown-item @if(session('admin_account_sort', 'last_updated') === 'last_updated') active @endif" href="{{ request()->fullUrlWithQuery(['sort' => 'last_updated']) }}">
                    <i class="las la-clock me-1"></i> @lang('Sort by Update Time')
                </a>
            </li>
            <li>
                <a class="dropdown-item @if(session('admin_account_sort') === 'cookie_health') active @endif" href="{{ request()->fullUrlWithQuery(['sort' => 'cookie_health']) }}">
                    <i class="las la-heartbeat me-1 text--danger"></i> @lang('Sort by Cookie Health')
                </a>
            </li>
            <li>
                <a class="dropdown-item @if(session('admin_account_sort') === 'created_at') active @endif" href="{{ request()->fullUrlWithQuery(['sort' => 'created_at']) }}">
                    <i class="las la-calendar me-1"></i> @lang('Sort by Newest (ID)')
                </a>
            </li>
            <li>
                <a class="dropdown-item @if(session('admin_account_sort') === 'title_asc') active @endif" href="{{ request()->fullUrlWithQuery(['sort' => 'title_asc']) }}">
                    <i class="las la-sort-alpha-down me-1"></i> @lang('Sort by Title (A-Z)')
                </a>
            </li>
        </ul>
    </div>
    <a href="{{ route('admin.social.media.index') }}" class="btn btn-sm btn-outline--secondary">
        <i class="las la-arrow-left"></i> @lang('Back to Platforms')
    </a>
    <button class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add Account for {{ $platform->name }}')">
        <i class="las la-plus"></i>@lang('Add Account')
    </button>
@endpush

@push('script')
<script>
    (function($){
        "use strict";
        $('.duplicateBtn').on('click', function () {
            var modal = $('#duplicateModal');
            var id = $(this).data('id');
            var title = $(this).data('title');
            modal.find('#duplicateTitle').val(title);
            var actionUrl = "{{ route('admin.account.listing.duplicate', ':id') }}".replace(':id', id);
            modal.find('#duplicateForm').attr('action', actionUrl);
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
