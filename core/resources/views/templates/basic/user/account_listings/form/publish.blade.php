@extends($activeTemplate . 'layouts.master')
@section('content')
    @php
        $accDesctiptionContent = getContent('sell_account_form.content', true);
    @endphp
    <div class="social-media-section py-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading">
                        <h2 class="section-heading__title mb-3"> @lang('Publish') </h2>
                        <p class="section-heading__desc m-auto"> {{ __(@$accDesctiptionContent->data_values->publish) }} </p>
                    </div>
                </div>
            </div>
            <div class="row gx-0">
                <div class="col-xl-3 col-md-5">
                    @include($activeTemplate . 'partials.progress_bar')
                </div>
                <div class="col-xl-9 col-md-7">
                    <div class="social-media__body d-flex align-items-center justify-content-center">
                        <div class="account-publish text-center">
                            <div class="account-publish-icon text--success">
                                <i class="far fa-check-circle"></i>
                            </div>
                            <h4 class="published-notify publish_message mb-3">{{ __($message) }} </h4>
                            <button class="btn btn--base publish_button {{ $button == '' ? 'd-none' : '' }}" id="saveAndPublish"> {{ __($button) }} </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            //Ajax
                $("#saveAndPublish").on('click', function () {

                var btnAfterSubmit = `<div class="spinner-border"></div> @lang('Saving')...`;
                var btn = $(this);
                btn.html(btnAfterSubmit);
                btn.attr('disabled', true);

                var formData = new FormData($('#biddingInfoForm')[0]);
                var url      = '{{ route('user.account.listing.publish.store', @$accountListing->id) }}';
                var token    = '{{ csrf_token() }}';

                formData.append('_token', token);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 'success') {
                            notify('success', "@lang('Publish request successfully')");
                            window.location.href = response.redirect_url
                        } else {
                            notify('error', response.message);
                            btn.removeAttr('disabled');
                        }
                    },
                    error: function(xhr, status, error) {
                        notify('error', error);
                        btn.removeAttr('disabled');
                    }
                });
            });

        })(jQuery);
    </script>
@endpush
