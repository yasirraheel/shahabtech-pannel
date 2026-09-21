@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0">@lang('Open Support Ticket')</h6>
                <a href="{{ route('ticket.index') }}" class="btn btn-sm btn--primary">
                    <i class="las la-list"></i> @lang('My Support Tickets')
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg--primary text-white py-3">
                    <h5 class="card-title text-white mb-0"><i class="las la-plus-circle me-1"></i> {{ __($pageTitle) }}</h5>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('ticket.store') }}" class="disableSubmission" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label class="fw-bold mb-1 required">@lang('Subject')</label>
                                <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" placeholder="@lang('Subject of your inquiry')" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label class="fw-bold mb-1 required">@lang('Priority')</label>
                                <select name="priority" class="form-control select2" data-minimum-results-for-search="-1" required>
                                    <option value="3">@lang('High')</option>
                                    <option value="2" selected>@lang('Medium')</option>
                                    <option value="1">@lang('Low')</option>
                                </select>
                            </div>
                            <div class="col-12 form-group mb-3">
                                <label class="fw-bold mb-1 required">@lang('Message')</label>
                                <textarea name="message" id="inputMessage" rows="6" class="form-control" placeholder="@lang('Describe your issue in detail...')" required>{{ old('message') }}</textarea>
                            </div>

                            <div class="col-md-9 mb-3">
                                <button type="button" class="btn btn--dark btn-sm addAttachment mb-2">
                                    <i class="las la-paperclip"></i> @lang('Add Attachment')
                                </button>
                                <p class="mb-2 text-muted" style="font-size: 13px;">
                                    <i class="las la-info-circle text--primary"></i> @lang('Max 5 files | Allowed: .jpg, .jpeg, .png, .pdf, .doc, .docx')
                                </p>
                                <div class="row fileUploadsContainer"></div>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button class="btn btn--primary btn-lg w-100" type="submit">
                                    <i class="las la-paper-plane me-1"></i> @lang('Submit Ticket')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            var fileAdded = 0;
            $('.addAttachment').on('click', function() {
                if (fileAdded >= 4) {
                    notify('error', 'You\'ve reached the maximum limit of files');
                    return false;
                }
                fileAdded++;
                $('.fileUploadsContainer').append(`
                    <div class="col-12 form-group mb-2">
                        <div class="input-group">
                            <input type="file" name="attachments[]" class="form-control" required accept=".png, .jpg, .jpeg, .pdf, .doc, .docx" />
                            <button type="button" class="btn btn--danger removeAttachment"><i class="las la-times"></i></button>
                        </div>
                    </div>
                `)
            });
            $(document).on('click', '.removeAttachment', function() {
                fileAdded--;
                $(this).closest('.form-group').remove();
            });
        })(jQuery);
    </script>
@endpush
