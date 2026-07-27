@extends($activeTemplate . 'layouts.' . $layout)
@section('content')
    <section class="py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card custom--card">
                        <div class="card-header card-header-bg d-flex justify-content-between align-items-center flex-wrap">
                            <h5 class="mt-0 text-white">
                                @php echo $myTicket->statusBadge; @endphp
                                [@lang('Ticket')#{{ $myTicket->ticket }}] {{ $myTicket->subject }}
                            </h5>
                            @if ($myTicket->status != Status::TICKET_CLOSE && $myTicket->user)
                                <button class="btn btn-danger close-button btn-sm confirmationBtn" data-question="@lang('Are you sure to close this ticket?')" data-action="{{ route('ticket.close', $myTicket->id) }}" type="button"><i class="fas fa-lg fa-times-circle"></i>
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <form class="disableSubmission" method="post" action="{{ route('ticket.reply', $myTicket->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row justify-content-between">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <textarea class="form-control form--control" name="message" rows="4" required>{{ old('message') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-9">
                                        <button class="btn btn--base btn-sm addAttachment my-2" type="button"> <i class="fas fa-plus"></i> @lang('Add Attachment') </button>
                                        <p class="mb-2"><span class="text--info">@lang('Max 5 files can be uploaded | Maximum upload size is ' . convertToReadableSize(ini_get('upload_max_filesize')) . ' | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx')</span></p>
                                        <div class="row fileUploadsContainer">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn--base w-100 my-2" type="submit"><i class="la la-fw la-lg la-reply"></i> @lang('Reply')
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card custom--card mt-4">
                        <div class="card-body">
                            @foreach ($messages as $message)
                                @if ($message->admin_id == 0)
                                    <div class="row border-radius-3 mx-2 my-3 border py-3">
                                        <div class="col-md-3 border-end text-end">
                                            <h5 class="my-3">{{ $message->ticket->name }}</h5>
                                        </div>
                                        <div class="col-md-9">
                                            <p class="text-muted fw-bold my-3">
                                                @lang('Posted on') {{ $message->created_at->format('l, dS F Y @ H:i') }}</p>
                                            <p>{{ $message->message }}</p>
                                            @if ($message->attachments->count() > 0)
                                                <div class="mt-2">
                                                    @foreach ($message->attachments as $k => $image)
                                                        @php
                                                            $ext = pathinfo($image->attachment, PATHINFO_EXTENSION);
                                                            $fileUrl = route('ticket.download', encrypt($image->id));
                                                            $downloadUrl = route('ticket.download', encrypt($image->id)) . '?download=1';
                                                        @endphp
                                                        <a class="me-3 view-attachment-btn" href="javascript:void(0)" data-url="{{ $fileUrl }}" data-download="{{ $downloadUrl }}" data-ext="{{ strtolower($ext) }}" data-title="@lang('Attachment') {{ ++$k }}"><i class="fa fa-file"></i> @lang('Attachment') {{ $k }} </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="row border-warning border-radius-3 mx-2 my-3 border py-3" style="background-color: #ffd96729">
                                        <div class="col-md-3 border-end text-end">
                                            <h5 class="my-3">{{ $message->admin->name }}</h5>
                                            <p class="lead text-muted">@lang('Staff')</p>
                                        </div>
                                        <div class="col-md-9">
                                            <p class="text-muted fw-bold my-3">
                                                @lang('Posted on') {{ $message->created_at->format('l, dS F Y @ H:i') }}</p>
                                            <p>{{ $message->message }}</p>
                                            @if ($message->attachments->count() > 0)
                                                <div class="mt-2">
                                                    @foreach ($message->attachments as $k => $image)
                                                        @php
                                                            $ext = pathinfo($image->attachment, PATHINFO_EXTENSION);
                                                            $fileUrl = route('ticket.download', encrypt($image->id));
                                                            $downloadUrl = route('ticket.download', encrypt($image->id)) . '?download=1';
                                                        @endphp
                                                        <a class="me-3 view-attachment-btn" href="javascript:void(0)" data-url="{{ $fileUrl }}" data-download="{{ $downloadUrl }}" data-ext="{{ strtolower($ext) }}" data-title="@lang('Attachment') {{ ++$k }}"><i class="fa fa-file"></i> @lang('Attachment') {{ $k }} </a>
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
        </div>
    </section>

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
                    <a href="#" id="attachmentDownloadBtn" class="btn btn--base btn-sm"><i class="las la-download"></i> @lang('Download File')</a>
                    <button type="button" class="btn btn--secondary btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

    @php
        $addClass = 'custom--modal';
    @endphp
    <x-confirmation-modal :addClass="$addClass" :customButton=true />

@endsection
@push('style')
    <style>
        .input-group-text:focus {
            box-shadow: none !important;
        }

        .reply-bg {
            background-color: #ffd96729
        }

        .empty-message img {
            width: 120px;
            margin-bottom: 15px;
        }
    </style>
@endpush
@push('script')
    <script>
        (function($) {
            "use strict";
            var fileAdded = 0;
            $('.addAttachment').on('click', function() {
                fileAdded++;
                if (fileAdded == 5) {
                    $(this).attr('disabled', true)
                }
                $(".fileUploadsContainer").append(`
                    <div class="col-lg-4 col-md-12 removeFileInput">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="file" name="attachments[]" class="form-control form--control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                                <button type="button" class="input-group-text removeFile bg--danger border--danger"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                `)
            });
            $(document).on('click', '.removeFile', function() {
                $('.addAttachment').removeAttr('disabled', true)
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
