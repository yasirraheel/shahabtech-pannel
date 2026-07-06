@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-body p-0">
                    <div class="table-responsive--lg table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Title')</th>
                                    <th>@lang('Social Media') </th>
                                    <th> @lang('Category') </th>
                                    <th> @lang('Plan') </th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountListings as $accountListing)
                                    <tr>
                                        <td>
                                            <p class="m-0">{{ strLimit($accountListing->title, 50) }}</p>
                                        </td>
                                        <td>
                                            {{ __(@$accountListing->socialMedia->name) }}
                                        </td>
                                        <td> {{ __(@$accountListing->category->name) }} </td>
                                        <td> {{ __(@$accountListing->plan->name) }} </td>
                                        <td> @php echo $accountListing->statusBadge; @endphp </td>
                                        <td>
                                            <div class="d-flex justify-content-end flex-wrap gap-1">
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
                            <label>@lang('URL')</label>
                            <input class="form-control" name="url" type="url" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Cookies (JSON array)')</label>
                            <textarea class="form-control" name="account_info" rows="4" placeholder='[{"name": "session", "value": "xyz"...}]' required></textarea>
                        </div>
                        <div class="form-group">
                            <label>@lang('Instructions')</label>
                            <textarea class="form-control" name="instructions" rows="3" placeholder="@lang('e.g. Please use the extension to login.')"></textarea>
                            <small class="text-muted">@lang('Optional. These instructions will be displayed on the platform card for subscribed users.')</small>
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
    <button class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add Account')">
        <i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush
