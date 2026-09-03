@extends('admin.layouts.app')

@section('panel')
    <!-- Top Summary Stat Cards (Exact Main Dashboard Style) -->
    <div class="row gy-4 mb-4">
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="6"
                link="{{ route('admin.warzone.links.index') }}"
                icon="las la-link"
                title="Total Links"
                value="{{ $totalCount }}"
                bg="primary"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="6"
                link="{{ route('admin.warzone.links.index', ['status' => 1]) }}"
                icon="las la-check-circle"
                title="Available (In Stock)"
                value="{{ $availableCount }}"
                bg="success"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="6"
                link="{{ route('admin.warzone.links.index', ['status' => 2]) }}"
                icon="las la-user-check"
                title="Active (In Use)"
                value="{{ $activeCount }}"
                bg="info"
            />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget
                style="6"
                link="{{ route('admin.warzone.links.index', ['status' => 3]) }}"
                icon="las la-archive"
                title="Used / Expired"
                value="{{ $usedCount + $expiredCount }}"
                bg="warning"
            />
        </div>
    </div>


    <!-- Main Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Product</th>
                                    <th>Link / Credential</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Purchased At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($links as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">#{{ $item->id }}</span>
                                            @if($item->order_id)
                                                <small class="d-block text-muted">{{ $item->order_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text--primary">{{ $item->product_name }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <code class="text-truncate d-inline-block" style="max-width: 260px;" id="linkText_{{ $item->id }}">{{ $item->link }}</code>
                                                <button type="button" class="btn btn-xs btn-outline--info copyBtn px-2 py-1" data-text="{{ $item->link }}" title="Copy Link">
                                                    <i class="las la-copy"></i>
                                                </button>
                                                @if(filter_var($item->link, FILTER_VALIDATE_URL))
                                                    <a href="{{ $item->link }}" target="_blank" class="btn btn-xs btn-outline--primary px-2 py-1" title="Open URL">
                                                        <i class="las la-external-link-alt"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php echo $item->sourceBadge; @endphp
                                        </td>
                                        <td>
                                            @php echo $item->statusBadge; @endphp
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $item->assigned_to ?: '--' }}</span>
                                        </td>
                                        <td>
                                            <span class="small">{{ showDateTime($item->purchased_at ?: $item->created_at, 'Y-m-d H:i') }}</span>
                                            <small class="d-block text-muted">{{ diffForHumans($item->purchased_at ?: $item->created_at) }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-sm btn-outline--primary editBtn"
                                                    data-id="{{ $item->id }}"
                                                    data-product_name="{{ $item->product_name }}"
                                                    data-link="{{ $item->link }}"
                                                    data-status="{{ $item->status }}"
                                                    data-assigned_to="{{ $item->assigned_to }}"
                                                    data-notes="{{ $item->notes }}"
                                                    title="Edit Status / Details">
                                                    <i class="las la-pen"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn"
                                                    data-question="Are you sure you want to delete this link record?"
                                                    data-action="{{ route('admin.warzone.links.delete', $item->id) }}"
                                                    title="Delete Link">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">No purchased links found yet. Links bought automatically or added manually will appear here.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($links->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($links) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Link Modal -->
    <div class="modal fade" id="addLinkModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="las la-plus-circle text-primary"></i> Add Purchased Links (Single or Batch)</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.warzone.links.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="product_name" value="Gemini AI Pro 18M" list="productOptions" required>
                                <datalist id="productOptions">
                                    <option value="Gemini AI Pro 18M">
                                    <option value="Netflix 1M Premium 4K HDR">
                                    <option value="ChatGPT Plus 1M">
                                    <option value="Canva Pro Lifetime">
                                    <option value="Prime Video 1M">
                                </datalist>
                                <small class="text-muted">Type or select product name.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Initial Status <span class="text-danger">*</span></label>
                                <select class="form-control" name="status" required>
                                    <option value="1" selected>Available (Ready to Use/Sell)</option>
                                    <option value="2">Active (Currently In Use)</option>
                                    <option value="3">Used (Assigned/Delivered)</option>
                                    <option value="0">Expired (Invalid)</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Link(s) / Credentials <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="links_batch" rows="5" placeholder="Paste link here... (You can paste multiple links, 1 link per line)" required></textarea>
                                <small class="text-muted">💡 Pro tip: If you paste multiple links (one per line), the system will automatically create an individual record for each link!</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Assigned To (Optional)</label>
                                <input type="text" class="form-control" name="assigned_to" placeholder="User name or email (optional)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Notes (Optional)</label>
                                <input type="text" class="form-control" name="notes" placeholder="e.g. Purchased from seller, batch #1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn--primary"><i class="las la-save"></i> Save Links</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Link Modal -->
    <div class="modal fade" id="editLinkModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="las la-pen text-primary"></i> Edit Link Details & Status</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form id="editLinkForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" class="form-control" name="product_name" id="editProductName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Link / Credential</label>
                            <textarea class="form-control" name="link" id="editLink" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-control" name="status" id="editStatus" required>
                                <option value="1">Available (In Stock)</option>
                                <option value="2">Active (In Use)</option>
                                <option value="3">Used (Assigned)</option>
                                <option value="0">Expired (Invalid)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assigned To</label>
                            <input type="text" class="form-control" name="assigned_to" id="editAssignedTo" placeholder="User name or email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea class="form-control" name="notes" id="editNotes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn--primary"><i class="las la-check"></i> Update Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Status Filter Dropdown -->
        <div class="dropdown">
            <button class="btn btn-sm btn-outline--secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="las la-filter"></i>
                @if(request()->status === '1') Available
                @elseif(request()->status === '2') Active
                @elseif(request()->status === '3') Used
                @elseif(request()->status === '0') Expired
                @else All Status
                @endif
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('admin.warzone.links.index') }}">All Status</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.warzone.links.index', ['status' => 1]) }}">🟢 Available</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.warzone.links.index', ['status' => 2]) }}">🔵 Active</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.warzone.links.index', ['status' => 3]) }}">⚪ Used</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.warzone.links.index', ['status' => 0]) }}">🔴 Expired</a></li>
            </ul>
        </div>

        <!-- Search Form -->
        <form action="" method="GET" class="d-inline">
            <div class="input-group">
                <input type="text" name="search" class="form-control form-control-sm bg--white" placeholder="Search link, product, order..." value="{{ request()->search }}">
                <button class="btn btn-sm btn--primary input-group-text"><i class="la la-search"></i></button>
            </div>
        </form>

        <!-- Add Link Button -->
        <button type="button" class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#addLinkModal">
            <i class="las la-plus"></i> Add Links
        </button>

        <!-- Link back to Warzone Telegram -->
        <a href="{{ route('admin.warzone.telegram.index') }}" class="btn btn-sm btn-outline--info">
            <i class="lab la-telegram"></i> Telegram Chat
        </a>
    </div>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";

        // Copy button
        $('.copyBtn').on('click', function() {
            const text = $(this).data('text');
            navigator.clipboard.writeText(text).then(function() {
                notify('success', 'Link copied to clipboard!');
            }).catch(function() {
                // Fallback
                const temp = $('<input>');
                $('body').append(temp);
                temp.val(text).select();
                document.execCommand('copy');
                temp.remove();
                notify('success', 'Link copied to clipboard!');
            });
        });

        // Edit button populate modal
        $('.editBtn').on('click', function() {
            const id = $(this).data('id');
            const productName = $(this).data('product_name');
            const link = $(this).data('link');
            const status = $(this).data('status');
            const assignedTo = $(this).data('assigned_to');
            const notes = $(this).data('notes');

            $('#editId').val(id);
            $('#editProductName').val(productName);
            $('#editLink').val(link);
            $('#editStatus').val(status);
            $('#editAssignedTo').val(assignedTo);
            $('#editNotes').val(notes);

            $('#editLinkForm').attr('action', '{{ route('admin.warzone.links.store') }}/' + id);
            $('#editLinkModal').modal('show');
        });
    })(jQuery);
</script>
@endpush
