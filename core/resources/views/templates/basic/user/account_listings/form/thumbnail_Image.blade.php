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
                        <h2 class="section-heading__title mb-3"> @lang('Thumbnail & Images') </h2>
                        <p class="section-heading__desc m-auto">
                            {{ @$accDesctiptionContent->data_values->thumbnail_and_images }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="row gx-0">
                <div class="col-xl-3 col-md-5">
                    @include(activeTemplate() . 'partials.progress_bar')
                </div>
                <div class="col-xl-9 col-md-7">
                    <div class="social-media__body">
                        <form id="urlImageForm">
                            <div class="form-group">
                                <label class="form--label">@lang('Thumbnails Image')</label>
                                <x-image-uploader name="thumbnail_image" type="account_listing_thumb" image="{{$accountListing->thumbnail_image }}" :required=false />
                            </div>
                            <div class="form-group">
                                <label class="form--label">@lang('Images/Screenshots')</label>
                                <div>
                                    <div class="input-images"></div>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            @lang('Supported Files:')
                                            <b>@lang('.png, .jpg, .jpeg')</b>
                                            @lang('& you can upload maximum ') <b>@lang('10')</b> @lang('images').
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn--base" id="saveAndContinue">
                                @if ($accountListing->step == 5)
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
    <script src="{{ asset('assets/global/js/image-uploader.min.js') }}"></script>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/global/css/image-uploader.min.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            // image uploder
            @if (isset($images))
                let preloaded = @json($images);
            @else
                let preloaded = [];
            @endif

            $('.input-images').imageUploader({
                preloaded: preloaded,
                imagesInputName: 'images',
                preloadedInputName: 'old',
                maxSize: 3 * 1024 * 1024,
                maxFiles: 10,
            });

            function proPicURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var preview = $(input).closest('.image-upload-wrapper').find('.image-upload-preview');
                        $(preview).css('background-image', 'url(' + e.target.result + ')');
                        $(preview).addClass('has-image');
                        $(preview).hide();
                        $(preview).fadeIn(650);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
            $(".image-upload-input").on('change', function() {
                proPicURL(this);
            });

            $(".remove-image").on('click', function() {
                $(this).parents(".image-upload-preview").css('background-image', 'none');
                $(this).parents(".image-upload-preview").removeClass('has-image');
                $(this).parents(".image-upload-wrapper").find('input[type=file]').val('');
            });

            // Ajax

            $("#urlImageForm").on('submit', function(e) {
                e.preventDefault();
                var btn = $(this).find(`button[type=submit]`);
                var prevText = btn.text();

                var btnAfterSubmit = `<div class="spinner-border"></div> @lang('Saving')...`;
                btn.html(btnAfterSubmit);
                btn.attr('disabled', true);

                //store
                var formData = new FormData($('#urlImageForm')[0]);
                var url = '{{ route('user.account.listing.thumbnail.image.store', @$accountListing->id) }}';

                var token = '{{ csrf_token() }}';
                formData.append('_token', token);

                function displayImage(input) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var preview = $(input).parent().find('.image-preview img');
                            $(preview).attr('src', e.target.result);
                            $(preview).addClass('has-image');
                            $(preview).hide();
                            $(preview).fadeIn(650);
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                $("#listingCoverUpload").on('change', function() {
                    displayImage(this);
                });

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
                                notify('success', "@lang('Image info updated successfully')");
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
        .image-uploader {
            min-height: 250px;
            position: relative;
            border-radius: 12px;
            border: 0;
            background: #32384a !important;
        }

        .image-upload-preview{
            border-color: #32384a !important;
        }

        .uploaded-image{
            border-radius: 8px;
            overflow: hidden;
        }

        .image-upload-preview{
            mix-blend-mode: soft-light;
        }
        
        .image-upload-preview.has-image,
        .image-upload-wrapper.hasDark .image-upload-preview{
            mix-blend-mode: unset;
        }

        .image-upload-input-wrapper i{
            color: hsl(var(--black))
        }

        .image-upload-input-wrapper label {
            border: 2px solid rgb(15 17 25 / 10%);
        }

        .image--uploader{
            max-width: 440px;
            width: 100%;
        }

    </style>
@endpush
