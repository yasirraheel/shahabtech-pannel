<div class="modal {{$addClass}} fade" id="confirmationModal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                @if ($customButton)
                    <button class="btn-close modal-icon" data-bs-dismiss="modal" type="button" aria-label="Close"> <i class="las la-times"></i></button>
                @else
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                @endif
            </div>
            <form method="POST">
                @csrf
                <div class="modal-body">
                    <p class="question"></p>
                </div>
                <div class="modal-footer {{ $customButton ? 'mt-3' : ''}}">
                    <button class="btn  {{$customButton ? 'btn-dark' : 'btn--dark'}} " data-bs-dismiss="modal" type="button">@lang('No')</button>
                    <button class="btn {{$customButton ? 'btn--base' : 'btn--primary'}}" type="submit">@lang('Yes')</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).on('click', '.confirmationBtn', function() {
                var modal = $('#confirmationModal');
                let data = $(this).data();
                modal.find('.question').text(`${data.question}`);
                modal.find('form').attr('action', `${data.action}`);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
