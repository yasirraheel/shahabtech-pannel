@extends($activeTemplate . 'layouts.' . $layout)
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0">@lang('Ticket Details')</h6>
                <a href="{{ route('ticket.index') }}" class="btn btn-sm btn--primary">
                    <i class="las la-arrow-left"></i> @lang('Back to Tickets')
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg--primary text-white d-flex justify-content-between align-items-center flex-wrap py-3">
                    <h5 class="card-title text-white mb-0">
                        @php echo $myTicket->statusBadge; @endphp
                        <span class="ms-2">[@lang('Ticket') #{{ $myTicket->ticket }}] {{ $myTicket->subject }}</span>
                    </h5>
                    @if ($myTicket->status != Status::TICKET_CLOSE && $myTicket->user)
                        <button class="btn btn-sm btn-danger confirmationBtn" data-question="@lang('Are you sure you want to close this ticket?')" data-action="{{ route('ticket.close', $myTicket->id) }}" type="button">
                            <i class="las la-times-circle"></i> @lang('Close Ticket')
                        </button>
                    @endif
                </div>
                <div class="card-body p-4">
                    <form class="disableSubmission" method="post" action="{{ route('ticket.reply', $myTicket->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row justify-content-between">
                            <div class="col-md-12 mb-3">
                                <label class="fw-bold mb-1 required">@lang('Your Reply')</label>
                                <textarea class="form-control" name="message" rows="4" placeholder="@lang('Type your reply here...')" required>{{ old('message') }}</textarea>
                            </div>

                            <div class="col-md-9 mb-3">
                                <button class="btn btn--dark btn-sm addAttachment mb-2" type="button">
                                    <i class="las la-paperclip"></i> @lang('Add Attachment')
                                </button>
                                <p class="mb-2 text-muted" style="font-size: 13px;">
                                    <i class="las la-info-circle text--primary"></i> @lang('Max 5 files | Allowed: .jpg, .jpeg, .png, .pdf, .doc, .docx')
                                </p>
                                <div class="row fileUploadsContainer"></div>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button class="btn btn--primary btn-lg w-100" type="submit">
                                    <i class="las la-paper-plane me-1"></i> @lang('Send Reply')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title text-dark mb-0"><i class="las la-comments me-1"></i> @lang('Conversation Thread')</h6>
                </div>
                <div class="card-body p-4">
                    @foreach ($messages as $message)
                        @if ($message->admin_id == 0)
                            <div class="row rounded mx-1 my-3 border p-3 bg-light">
                                <div class="col-md-3 border-end">
                                    <h6 class="mb-1 text--primary">{{ $message->ticket->name }}</h6>
                                    <span class="badge badge--dark">@lang('You')</span>
                                </div>
                                <div class="col-md-9">
                                    <p class="text-muted small mb-2">
                                        <i class="far fa-clock me-1"></i> {{ $message->created_at->format('l, dS F Y @ H:i') }}
                                    </p>
                                    <p class="mb-2 text-dark">{{ $message->message }}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="mt-2 pt-2 border-top">
                                            @foreach ($message->attachments as $k => $image)
                                                @php
                                                    $ext = pathinfo($image->attachment, PATHINFO_EXTENSION);
                                                    $fileUrl = route('ticket.download', encrypt($image->id));
                                                    $downloadUrl = route('ticket.download', encrypt($image->id)) . '?download=1';
                                                @endphp
                                                <a class="me-3 btn btn-sm btn-outline--dark view-attachment-btn" href="javascript:void(0)" data-url="{{ $fileUrl }}" data-download="{{ $downloadUrl }}" data-ext="{{ strtolower($ext) }}" data-title="@lang('Attachment') {{ ++$k }}">
                                                    <i class="las la-file-download me-1"></i> @lang('Attachment') {{ $k }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="row rounded mx-1 my-3 border p-3" style="background-color: #f0f7ff; border-left: 4px solid #0d6efd !important;">
                                <div class="col-md-3 border-end">
                                    <h6 class="mb-1 text--primary">{{ $message->admin->name }}</h6>
                                    <span class="badge badge--success">@lang('Support Staff')</span>
                                </div>
                                <div class="col-md-9">
                                    <p class="text-muted small mb-2">
                                        <i class="far fa-clock me-1"></i> {{ $message->created_at->format('l, dS F Y @ H:i') }}
                                    </p>
                                    <p class="mb-2 text-dark">{{ $message->message }}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="mt-2 pt-2 border-top">
                                            @foreach ($message->attachments as $k => $image)
                                                @php
                                                    $ext = pathinfo($image->attachment, PATHINFO_EXTENSION);
                                                    $fileUrl = route('ticket.download', encrypt($image->id));
                                                    $downloadUrl = route('ticket.download', encrypt($image->id)) . '?download=1';
                                                @endphp
                                                <a class="me-3 btn btn-sm btn-outline--primary view-attachment-btn" href="javascript:void(0)" data-url="{{ $fileUrl }}" data-download="{{ $downloadUrl }}" data-ext="{{ strtolower($ext) }}" data-title="@lang('Attachment') {{ ++$k }}">
                                                    <i class="las la-file-download me-1"></i> @lang('Attachment') {{ $k }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="attachmentPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachmentPreviewTitle"><i class="las la-paperclip me-1"></i> @lang('Attachment Preview')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3" id="attachmentPreviewBody">
                </div>
                <div class="modal-footer">
                    <a href="#" id="attachmentDownloadBtn" class="btn btn--primary btn-sm"><i class="las la-download"></i> @lang('Download File')</a>
                    <button type="button" class="btn btn--dark btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal addClass="custom--modal" :customButton=true />

@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            var fileAdded = 0;
            $('.addAttachment').on('click', function() {
                fileAdded++;
                if (fileAdded == 5) {
                    $(this).attr('disabled', true);
                }
                $(".fileUploadsContainer").append(`
                    <div class="col-12 form-group mb-2 removeFileInput">
                        <div class="input-group">
                            <input type="file" name="attachments[]" class="form-control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                            <button type="button" class="btn btn--danger removeFile"><i class="las la-times"></i></button>
                        </div>
                    </div>
                `);
            });
            $(document).on('click', '.removeFile', function() {
                $('.addAttachment').removeAttr('disabled');
                fileAdded--;
                $(this).closest('.removeFileInput').remove();
            });

            $(document).on('click', '.view-attachment-btn', function(e) {
                e.preventDefault();
                var fileUrl = $(this).data('url');
                var downloadUrl = $(this).data('download');
                var ext = $(this).data('ext');
                var title = $(this).data('title');

                $('#attachmentPreviewTitle').text(title + ' (.' + ext + ')');
                $('#attachmentDownloadBtn').attr('href', downloadUrl);

                var content = '';
                var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (imageExts.includes(ext)) {
                    content = '<img src="' + fileUrl + '" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">';
                } else if (ext === 'pdf') {
                    content = '<iframe src="' + fileUrl + '" style="width: 100%; height: 60vh; border: none;" class="rounded"></iframe>';
                } else {
                    content = '<div class="p-4"><i class="las la-file-alt" style="font-size: 64px; color: #6c757d;"></i><p class="mt-2 text-muted">' + title + '</p></div>';
                }

                $('#attachmentPreviewBody').html(content);
                $('#attachmentPreviewModal').modal('show');
            });
        })(jQuery);
    </script>
@endpush
