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
                        <span class="section-heading__subtitle"> {{ __($pageTitle) }} </span>
                        <h2 class="section-heading__title"> @lang('Account Credentials') </h2>
                        <p class="section-heading__desc m-auto">
                            {{ @$accDesctiptionContent->data_values->account_credentials }} </p>
                    </div>
                </div>
            </div>
            <div class="row gx-0">
                <div class="col-xl-3 col-md-5">
                    @include(activeTemplate() . 'partials.progress_bar')
                </div>
                <div class="col-xl-9 col-md-7">
                    <div class="social-media__body">
                        <form id="accCredentialsForm">
                            <div class="form-group">
                                <label class="form--label" for="">@lang('Username')</label>
                                <input class="form--control" name="username" type="text" value="{{ old('username', @$accountListing->accountCredential->username) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form--label" for="">@lang('Email')</label>
                                <input class="form--control" name="email" type="text"
                                    value="{{ old('email', @$accountListing->accountCredential->email) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form--label">@lang('Password')</label>
                                <div class="position-relative">
                                    <input class="form-control form--control exclude" id="your-password" name="password"
                                        type="password"
                                        value="{{ isset($accountListing->accountCredential->password) ? $accountListing->accountCredential->password : '' }}"
                                        required>
                                    <span class="password-show-hide fas fa-eye toggle-password fa-eye-slash"
                                        id="#your-password"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form--label" for="">@lang('Mobile Number')</label>
                                <input class="form--control" name="mobile_number" type="text"
                                    value="{{ old('mobile_number', @$accountListing->accountCredential->mobile_number) }}"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form--label" for="">@lang('Others Information')</label>
                                <textarea class="form--control" name="others_info" rows="3">{{ old('others_info', @$accountListing->accountCredential->others_info) }}</textarea>
                            </div>
                            <button class="btn btn--base " id="saveAndContinue" type="submit">
                                @if ($accountListing->step == 4)
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
                $("#accCredentialsForm").on('submit', function (e) {
                e.preventDefault();

                var btn            = $(this).find(`button[type=submit]`);
                var prevText       = btn.text();
                var btnAfterSubmit = `<div class="spinner-border"></div> @lang('Saving')...`;

                btn.html(btnAfterSubmit);
                btn.attr('disabled', true);

                //store
                var formData = new FormData($('#accCredentialsForm')[0]);
                var url = '{{ route('user.account.listing.account.credential.store', @$accountListing->id) }}';
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
                                notify('success', "@lang('Account credential updated successfully')");
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
