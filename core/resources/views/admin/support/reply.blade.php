@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body ">

                    <h6 class="card-title  mb-4">
                        <div class="row align-items-center">
                            <div class="col-sm-8 col-md-6">
                                @php echo $ticket->statusBadge; @endphp
                                [@lang('Ticket#'){{ $ticket->ticket }}] {{ $ticket->subject }}
                            </div>
                            <div class="col-sm-4  col-md-6 text-sm-end mt-sm-0 mt-3">
                                @if ($ticket->status != Status::TICKET_CLOSE)
                                    <button class="btn btn--danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#DelModal">
                                        <i class="la la-times"></i> @lang('Close Ticket')
                                    </button>
                                @endif
                            </div>
                        </div>
                    </h6>



                    <form action="{{ route('admin.ticket.reply', $ticket->id) }}" enctype="multipart/form-data" method="post" class="form-horizontal disableSubmission">
                        @csrf


                        <div class="row ">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <textarea class="form-control" name="message" rows="5" required id="inputMessage" placeholder="@lang('Enter reply here')"></textarea>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <button type="button" class="btn btn--dark addAttachment my-2"> <i class="fas fa-plus"></i> @lang('Add Attachment') </button>
                                <p class="mb-2"><span class="text--info">@lang('Max 5 files can be uploaded | Maximum upload size is '.convertToReadableSize(ini_get('upload_max_filesize')) .' | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx')</span></p>
                                <div class="row fileUploadsContainer">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn--primary w-100 my-2" type="submit" name="replayTicket" value="1"><i class="la la-fw la-lg la-reply"></i> @lang('Reply')
                                </button>
                            </div>
                        </div>

                    </form>


                    @foreach ($messages as $message)
                        @if ($message->admin_id == 0)
                            <div class="row border border--primary border-radius-3 my-3 mx-0">

                                <div class="col-md-3 border-end text-md-end text-start">
                                    <h5 class="my-3">{{ $ticket->name }}</h5>
                                    @if ($ticket->user_id != null)
                                        <p><a href="{{ route('admin.users.detail', $ticket->user_id) }}">&#64;{{ $ticket->name }}</a></p>
                                    @else
                                        <p>@<span>{{ $ticket->name }}</span></p>
                                    @endif
                                    <button class="btn btn--danger btn-sm my-3 confirmationBtn" data-question="@lang('Are you sure to delete this message?')" data-action="{{ route('admin.ticket.delete', $message->id) }}"><i class="la la-trash"></i> @lang('Delete')</button>
                                </div>

                                <div class="col-md-9">
                                    <p class="text-muted fw-bold my-3">
                                        @lang('Posted on') {{ showDateTime($message->created_at, 'l, dS F Y @ h:i a') }}</p>
                                    <p>{{ $message->message }}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="my-3">
                                            @foreach ($message->attachments as $k => $image)
                                                @php
                                                    $ext = pathinfo($image->attachment, PATHINFO_EXTENSION);
                                                    $fileUrl = route('admin.ticket.download', encrypt($image->id));
                                                    $downloadUrl = route('admin.ticket.download', encrypt($image->id)) . '?download=1';
                                                @endphp
                                                <a href="javascript:void(0)" class="me-2 view-attachment-btn" data-url="{{ $fileUrl }}" data-download="{{ $downloadUrl }}" data-ext="{{ strtolower($ext) }}" data-title="@lang('Attachment') {{ ++$k }}"><i class="fa-regular fa-file"></i> @lang('Attachment') {{ $k }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="row border border-warning border-radius-3 my-3 mx-0 admin-bg-reply">

                                <div class="col-md-3 border-end text-md-end text-start">
                                    <h5 class="my-3">{{ @$message->admin->name }}</h5>
                                    <p class="lead text-muted">@lang('Staff')</p>
                                    <button class="btn btn--danger btn-sm my-3 confirmationBtn" data-question="@lang('Are you sure to delete this message?')" data-action="{{ route('admin.ticket.delete', $message->id) }}"><i class="la la-trash"></i> @lang('Delete')</button>
                                </div>

                                <div class="col-md-9">
                                    <p class="text-muted fw-bold my-3">
                                        @lang('Posted on') {{ showDateTime($message->created_at, 'l, dS F Y @ h:i a') }}</p>
                                    <p>{{ $message->message }}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="my-3">
                                            @foreach ($message->attachments as $k => $image)
                                                <a href="{{ route('admin.ticket.download', encrypt($image->id)) }}" class="me-2"><i class="fa-regular fa-file"></i> @lang('Attachment') {{ ++$k }} </a>
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




    <div class="modal fade" id="DelModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> @lang('Close Support Ticket!')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>@lang('Are you want to close this support ticket?')</p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="{{ route('admin.ticket.close', $ticket->id) }}">
                        @csrf
                        <input type="hidden" name="replayTicket" value="2">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal"> @lang('No') </button>
                        <button type="submit" class="btn btn--primary"> @lang('Yes') </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="attachmentPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachmentPreviewTitle"><i class="las la-paperclip me-1"></i> @lang('Attachment Preview')</h5>
                    <button type="button" class="close btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
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
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.ticket.index') }}" />
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {
            $('.delete-message').on('click', function(e) {
                $('.message_id').val($(this).data('id'));
            })
            var fileAdded = 0;
            $('.addAttachment').on('click', function() {
                fileAdded++;
                if (fileAdded == 5) {
                    $(this).attr('disabled',true)
                }
                $(".fileUploadsContainer").append(`
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 removeFileInput">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="file" name="attachments[]" class="form-control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                            <button type="button" class="input-group-text removeFile bg--danger border--danger"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
                `)
            });

            $(document).on('click', '.removeFile', function() {
                $('.addAttachment').removeAttr('disabled',true)
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
