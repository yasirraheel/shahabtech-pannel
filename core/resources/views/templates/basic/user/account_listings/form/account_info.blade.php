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
                        <h2 class="section-heading__title mb-3"> @lang('Account Information') </h2>
                        <p class="section-heading__desc m-auto">
                            {{ @$accDesctiptionContent->data_values->account_information }} </p>
                    </div>
                </div>
            </div>
            <div class="row gx-0">
                <div class="col-xl-3 col-md-5">
                    @include(activeTemplate() . 'partials.progress_bar')
                </div>
                <div class="col-xl-9 col-md-7">
                    <div class="social-media__body">
                        <form id="accountInfoForm">
                            <div>
                                <x-viser-form identifier="id" identifierValue="{{ @$accountListing->socialMedia->form_id }}" />
                            </div>
                            <div class="social-media__check form-group">
                                <div class="form--check">
                                    <input class="form-check-input" id="verified" name="is_verified" type="checkbox"
                                        value="1" @checked($accountListing->is_verified)>
                                    <label class="form-check-label" for="verified">
                                        @lang('Verified Account')
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--base " id="saveAndContinue">
                                @if ($accountListing->step == 3)
                                    @lang('Save & Continue')
                                @else
                                    @lang('Save')
                                @endif
                            </button>
                        </form>
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
            $('#accountInfoForm').on('submit',function(e) {
                e.preventDefault();
                var btn = $(this).find(`button[type=submit]`);
                var prevText = btn.text();
                var btnAfterSubmit = `<div class="spinner-border"></div> @lang('Saving')...`;
                btn.html(btnAfterSubmit);
                btn.attr('disabled', true);

                //store
                var formData = new FormData($('#accountInfoForm')[0]);
                var url = '{{ route('user.account.listing.account.info.store', @$accountListing->id) }}';
                var token = '{{ csrf_token() }}';
                formData.append('_token', token);

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
                                notify('success', "@lang('Account information updated successfully')");
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


            @if ($accountListing->account_info)
                let accountInformation = @json($accountListing->account_info);
                $(accountInformation).each(function(index, information) {
                    let name = removeSpecialCharacters(titleToKey(information.name));
                    let targetElement = $(`[data-name=${name}]`);
                    let type = information.type;
                    if (type == 'file') return;
                    if (type == 'select' || type == 'text') targetElement.val(information.value);
                    if (type == 'textarea') targetElement.text(information.value);

                    if (type == 'checkbox') {
                        if (typeof information.value == 'string') {
                            targetElement.val(information.value);
                            targetElement.prop('checked', true);
                        } else {
                            let value = Array.from(information.value);
                            $(targetElement).each(function(index, ele) {
                                if (value.indexOf(ele.value) != -1) {
                                    $(ele).prop('checked', true)
                                }
                            });
                        }
                    }
                    if (information.type == 'radio') {
                        $(targetElement).each(function(index, elem) {
                            if (elem.value == information.value) {
                                $(elem).prop('checked', true)
                            }
                        });
                    }
                });
            @endif


            function removeSpecialCharacters(text) {
                return text.replace(/[^a-zA-Z0-9_]/g, '');
            }

            function titleToKey(text) {
                return text.toLowerCase().replace(/ /g, '_');
            }


        })(jQuery);
    </script>
@endpush
