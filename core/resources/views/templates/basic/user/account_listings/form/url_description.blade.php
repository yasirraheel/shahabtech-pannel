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
                        <h2 class="section-heading__title mb-3"> @lang('Accounts URL & Description') </h2>
                        <p class="section-heading__desc m-auto">
                            {{ @$accDesctiptionContent->data_values->url_and_description }} </p>
                    </div>
                </div>
            </div>
            <div class="row gx-0">
                <div class="col-xl-3 col-md-5">
                    @include(activeTemplate() . 'partials.progress_bar')
                </div>
                <div class="col-xl-9 col-md-7">
                    <div class="social-media__body">
                        <form id="urlDescriptionForm">
                            <div class="form-group">
                                <label class="form--label"> @lang('Account Title') </label>
                                <input class="form--control" name="title" type="text"
                                    value="{{ old('title', $accountListing->title) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form--label"> @lang('Account Url') </label>
                                <input class="form--control" name="url" type="url"
                                    value="{{ old('url', $accountListing->url) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form--label" for="description"> @lang('Account Description') </label>
                                <textarea class="form--control nicEdit" name="description"> @php echo $accountListing->description @endphp </textarea>
                            </div>
                            <button class="btn btn--base " id="saveAndContinue" type="submit">
                                @if ($accountListing->step == 2)
                                    @lang('Save & Continue')
                                @else
                                    @lang('Save as draft')
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-lib')
    <script src="{{ asset('assets/global/js/nicEdit.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            //Nice edit
            bkLib.onDomLoaded(function() {
                $(".nicEdit").each(function(index) {
                    $(this).attr("id", "nicEditor" + index);
                    new nicEditor({
                        fullPanel: true
                    }).panelInstance('nicEditor' + index, {
                        hasPanel: true
                    });
                });
            });

            $(document).on('mouseover ', '.nicEdit-main,.nicEdit-panelContain', function() {
                $('.nicEdit-main').focus();
            });

            //Ajax
            $('#urlDescriptionForm').on("submit",function(e) {
                e.preventDefault();

                var btn            = $(this).find(`button[type=submit]`);
                var prevText       = btn.text();
                var btnAfterSubmit = `<div class="spinner-border"></div> @lang('Saving')...`;

                btn.html(btnAfterSubmit);
                btn.attr('disabled', true);

                //store
                var formData = new FormData($('#urlDescriptionForm')[0]);

                var url = '{{ route('user.account.listing.url.description.store', @$accountListing->id) }}';
                var token = '{{ csrf_token() }}';

                var description = $('.nicEdit-main').html();

                formData.append('_token', token);
                formData.append('description', description);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == 'success') {
                            if (!response.is_update) {
                                window.location.href = response.redirect_url
                            } else {
                                notify('success', '@lang('Account url & description updated successfully')');
                                btn.removeAttr('disabled');
                            }
                        } else {
                            notify('error', response.message);
                            btn.removeAttr('disabled');
                        }
                        btn.text(prevText);
                    },
                    error: function(xhr, status, error) {
                        notify('error', error);
                        btn.removeAttr('disabled');
                        btn.text(prevText);
                    }
                });
            });

        })(jQuery);
    </script>
@endpush
