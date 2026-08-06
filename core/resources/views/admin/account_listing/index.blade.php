@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            @if(!empty($selectedPlatforms))
                <div class="alert alert border--primary bg--white d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <i class="las la-filter text--primary me-1 fs-5"></i>
                        <span class="fw-bold me-2">@lang('Active Filter:')</span>
                        @foreach($socialMedias->whereIn('id', $selectedPlatforms) as $activeSm)
                            <span class="badge badge--primary me-1">{{ $activeSm->name }}</span>
                        @endforeach
                        <small class="text-muted ms-1">(@lang('Persistent filter active'))</small>
                    </div>
                    <a href="{{ route('admin.account.listing.index', ['reset_filter' => 1]) }}" class="btn btn-sm btn-outline--danger">
                        <i class="las la-times"></i> @lang('Clear Filter')
                    </a>
                </div>
            @endif

            <div class="card ">
                <div class="card-body p-0">
                    <div class="table-responsive--lg table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Title')</th>
                                    <th>@lang('Social Media') </th>
                                    <th> @lang('Assigned Users') </th>
                                    <th>@lang('Cookie Health')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountListings as $accountListing)
                                    <tr>
                                        <td>
                                            <p class="m-0 fw-bold">{{ strLimit($accountListing->title, 50) }}</p>
                                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                                <i class="las la-clock"></i> {{ diffForHumans($accountListing->updated_at) }}
                                            </small>
                                        </td>
                                        <td>
                                            {{ __(@$accountListing->socialMedia->name) }}
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.all') }}?account_id={{ $accountListing->id }}" class="badge badge--info" style="font-size: 11px; padding: 4px 8px; text-decoration: none;" title="@lang('Click to view assigned users')">
                                                <i class="las la-users"></i> {{ $accountListing->assignedUsersCount() }} @lang('Users')
                                            </a>
                                        </td>
                                        <td> @php echo $accountListing->cookieStatusBadge; @endphp </td>
                                        <td> @php echo $accountListing->statusBadge; @endphp </td>
                                        <td>
                                            <div class="d-flex justify-content-end flex-wrap gap-1">
                                                <a href="{{ route('admin.account.listing.check.cookie', $accountListing->id) }}" class="btn btn-outline--dark btn-sm" title="@lang('Check Cookie Health Now')">
                                                    <i class="las la-cookie"></i>@lang('Check Cookie')
                                                </a>
                                                <button class="btn btn-outline--primary editBtn cuModalBtn btn-sm" data-modal_title="@lang('Update Account')" data-resource="{{ $accountListing }}">
                                                    <i class="las la-pen"></i>@lang('Edit')
                                                </button>
                                                @if ($accountListing->status == Status::LISTING_ACTIVE)
                                                    <button class="btn btn-outline--danger btn-sm confirmationBtn" data-question="@lang('Are you sure to disable this account?')" data-action="{{ route('admin.account.listing.status', $accountListing->id) }}">
                                                        <i class="las la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline--success confirmationBtn btn-sm" data-question="@lang('Are you sure to enable this account?')" data-action="{{ route('admin.account.listing.status', $accountListing->id) }}">
                                                        <i class="las la-eye"></i>@lang('Enable')
                                                    </button>
                                                @endif
                                                <button class="btn btn-outline--success btn-sm confirmationBtn" data-question="@lang('Are you sure you want to extend cookie expiry by 30 days?')" data-action="{{ route('admin.account.listing.modify.expiry', ['id' => $accountListing->id, 'action' => 'extend']) }}">
                                                    <i class="las la-calendar-plus"></i>@lang('+30 Days')
                                                </button>
                                                <button class="btn btn-outline--warning btn-sm confirmationBtn" data-question="@lang('Are you sure you want to decrease cookie expiry by 30 days?')" data-action="{{ route('admin.account.listing.modify.expiry', ['id' => $accountListing->id, 'action' => 'decrease']) }}">
                                                    <i class="las la-calendar-minus"></i>@lang('-30 Days')
                                                </button>
                                                <button class="btn btn-outline--info btn-sm duplicateBtn" data-id="{{ $accountListing->id }}" data-title="{{ $accountListing->title }} - Copy">
                                                    <i class="las la-copy"></i>@lang('Duplicate')
                                                </button>
                                                <button class="btn btn-outline--danger btn-sm confirmationBtn" data-question="@lang('Are you sure you want to delete this account?')" data-action="{{ route('admin.account.listing.delete', $accountListing->id) }}">
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
                @if ($accountListings->hasPages())
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

    <div class="modal fade" id="cuModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.account.listing.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Title')</label>
                            <input class="form-control" name="title" type="text" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Platform / Social Media')</label>
                            <select class="form-control" name="social_media_id" required>
                                <option value="">@lang('Select Platform')</option>
                                @foreach($socialMedias as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Category')</label>
                            <select class="form-control" name="category_id" required>
                                <option value="">@lang('Select Category')</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Plan')</label>
                            <select class="form-control" name="plan_id">
                                <option value="">@lang('No Plan (Direct Access)')</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('URL')</label>
                            <input class="form-control" name="url" type="url" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Cookies / Account Info (JSON)')</label>
                            <textarea class="form-control" name="account_info" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>@lang('Instructions')</label>
                            <textarea class="form-control" name="instructions" rows="3"></textarea>
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
    <form action="{{ route('admin.account.listing.index') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center justify-content-end">
        <input type="hidden" name="filter_applied" value="1">
        <div style="min-width: 220px; max-width: 320px;">
            <select name="platforms[]" class="form-control select2" multiple="multiple" data-placeholder="@lang('Filter Platforms')">
                @foreach($socialMedias as $sm)
                    <option value="{{ $sm->id }}" @selected(in_array($sm->id, $selectedPlatforms))>{{ $sm->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-outline--primary">
            <i class="las la-filter"></i> @lang('Filter')
        </button>
        @if(!empty($selectedPlatforms))
            <a href="{{ route('admin.account.listing.index', ['reset_filter' => 1]) }}" class="btn btn-outline--danger" title="@lang('Clear Filter')">
                <i class="las la-times"></i> @lang('Clear')
            </a>
        @endif
        <div class="dropdown d-inline-block">
            <button class="btn btn-outline--secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="las la-sort-amount-down me-1"></i>
                @if(session('admin_account_sort', 'last_updated') === 'last_updated')
                    @lang('Sort: Update Time')
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
        <x-search-form placeholder="Search Title" />
        <button class="btn btn-outline--primary cuModalBtn" data-modal_title="@lang('Add Account')">
            <i class="las la-plus"></i>@lang('Add New')
        </button>
    </form>
@endpush

@push('script')
<script>
    (function($){
        "use strict";
        if ($.fn.select2) {
            $('.select2').select2({
                placeholder: "@lang('Filter Platforms')",
                allowClear: true
            });
        }
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
