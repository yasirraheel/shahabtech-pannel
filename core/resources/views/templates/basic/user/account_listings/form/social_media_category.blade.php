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
                        <h2 class="section-heading__title mb-3"> @lang('Platform & Category') </h2>
                        <p class="section-heading__desc m-auto">
                            {{ @$accDesctiptionContent->data_values->social_media_category }} </p>
                    </div>
                </div>
            </div>
            <div class="row gx-0">
                <div class="col-xl-3 col-md-5">
                    @include(activeTemplate() . 'partials.progress_bar')
                </div>
                <div class="col-xl-9 col-md-7">
                    <div class="social-media__body">
                        <form id="categoryForm">
                            <div class="form-group position-relative">
                                <label class="form--label"> @lang('Platform') </label>
                                <select class="form--control select2" name="social_media" required>
                                    <option value=""> @lang('Select one') </option>
                                    @foreach ($socialsMedia as $socialMedia)
                                        <option value="{{ $socialMedia->id }}" @selected(old('social_media', $socialMedia->id == @$accountListing->social_media_id))>
                                            {{ __($socialMedia->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group position-relative">
                                <label class="form--label"> @lang('Category')</label>
                                <select class="form--control select2" name="category" required>
                                    <option value="">@lang('Select one')</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category', $category->id == @$accountListing->category_id))>
                                            {{ __($category->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn--base "  type="submit">
                                @if (empty($accountListing->step))
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

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

@push('style')
    <link href="{{ asset('assets/global/css/select2.min.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $.each($('.select2-basic'), function(i, element) {
                $(element).select2({
                    dropdownParent: (element).closest(".position-relative")
                });
            });

            //Ajax
            $('#categoryForm').on('submit',function(e) {
                e.preventDefault();

                var btn = $(this).find(`button[type=submit]`);
                var prevText = btn.text();

                var btnAfterSubmit = `<div class="spinner-border"></div> @lang('Saving')...`;
                btn.html(btnAfterSubmit);
                btn.attr('disabled', true);

                //store
                var formData = new FormData($('#categoryForm')[0]);
                var url      = '{{ route('user.account.listing.social.media.category.store', @$accountListing->id) }}';
                var token    = '{{ csrf_token() }}';
                formData.append('_token', token);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log(response);
                        if (response.status == 'success') {
                            if (!response.is_update) {
                                window.location.href = response.redirect_url;
                            } else {
                                notify('success', "@lang('Category updated successfully')");
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

@push('style')
    <style>
        .select2-search--dropdown .select2-search__field {
            width: 97%;
        }
    </style>
@endpush
