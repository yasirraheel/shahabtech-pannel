@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="py-5">
        <div class="container">
            <div class="card mb-5">
                @include(activeTemplate().'user.account_listings.progress_bar')
            </div>
            <div class="card">
                <div class="card-header">
                    <h4>{{ $pageTitle }}</h4>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('Social Media')</label>
                                    <select class="form-control" name="social_media_id" required>
                                        <option value="">@lang('Select')</option>
                                        @foreach ($socialsMedia as $socialMedia)
                                            <option value="{{ $socialMedia->id }}" @selected(old('category_id') == $socialMedia->id)> {{ $socialMedia->name }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('Category')</label>
                                    <select class="form-control" name="category_id" required>
                                        <option value="">@lang('Select')</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)> {{ $category->name }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('Auction End Date')</label>
                                    <input name="auction_deadline" type="text" data-range="false"  data-language="en" data-format="Y-m-d" class="datepicker-here form-control bg--white pe-2" data-position='bottom right' placeholder="@lang('Date')" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('Minimum Price')</label>
                                    <div class="input-group">
                                        <input class="form-control" name="min_price" type="number" value="{{ old('min_price') }}" required>
                                        <span class="input-group-text">{{ gs('cur_sym') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">@lang('Description')</label>
                                    <textarea class="form-control nicEdit" name="description" rows="5" required>{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Thumbnails Image')</label>
                                    <div class="image-upload">
                                        <div class="thumb">
                                            <div class="profilePicPreview" style="background-image: url({{ getImage(getFilePath('account_listing_thumb') . '/' . null, getFileSize('account_listing_thumb')) }})">
                                                <button class="remove-image" type="button"><i class="fa fa-times"></i></button>
                                            </div>
                                            <div class="avatar-edit">
                                                <input class="profilePicUpload" id="profilePicUpload" name="image" type="file" accept=".png, .jpg, .jpeg">
                                                <label class="bg--success" for="profilePicUpload">@lang('Upload Image')</label>
                                                <small class="mt-2">@lang('Supported files'): <b>@lang('jpeg'),
                                                        @lang('jpg'),
                                                        @lang('png').</b>
                                                    @lang('Image will be resized into '){{ getFileSize('account_listing_thumb') }}@lang('px')
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="">@lang('Images/Screenshots')</label>
                                    <div>
                                        <div class="input-images pb-3"></div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('Username')</label>
                                    <input class="form-control" name="username" type="text" value="{{ old('username') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('Email')</label>
                                    <input class="form-control" name="email" type="text" value="{{ old('email') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('Password')</label>
                                    <input class="form-control" name="password" type="text" value="{{ old('password') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('Mobile Number')</label>
                                    <input class="form-control" name="mobile_number" type="text" value="{{ old('mobile_number') }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">@lang('Others Information')</label>
                                    <textarea name="others_info" class="form-control" rows="3">{{old('others_info')}}</textarea>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('style')
    <style>
        .form--control,
        .image-uploader {
            border-radius: 0.5rem;
        }

        .image-uploader {
            /* border: 1px solid hsl(var(--black)/0.1); */
            border: 1px solid #000000;
        }

        .image-preview {
            margin-top: .8rem;
            width: 217px;
            height: 126px;
            padding: 2px;
            /* border: 1px solid hsl(var(--black)/0.1); */
            border: 1px solid #000000;
            border-radius: 3px;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .input-group-text {
            background-color: #fbfbfb;
            border: 1px solid #e9e9e9;
            border-radius: .5rem;
        }

        :focus-visible {
            outline: none !important;
        }
    </style>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/nicEdit.js') }}"></script>
    <script src="{{ asset('assets/global/js/image-uploader.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/global/css/image-uploader.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/admin/css/vendor/datepicker.min.css')}}">
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

            //global js
            function proPicURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var preview = $(input).parents('.thumb').find('.profilePicPreview');
                        $(preview).css('background-image', 'url(' + e.target.result + ')');
                        $(preview).addClass('has-image');
                        $(preview).hide();
                        $(preview).fadeIn(650);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
            $(".profilePicUpload").on('change', function() {
                proPicURL(this);
            });

            $(".remove-image").on('click', function() {
                $(this).parents(".profilePicPreview").css('background-image', 'none');
                $(this).parents(".profilePicPreview").removeClass('has-image');
                $(this).parents(".thumb").find('input[type=file]').val('');
            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .image-upload .thumb .profilePicPreview {
            width: 100%;
            height: 310px;
            display: block;
            border: 3px solid #f1f1f1;
            box-shadow: 0 0 5px 0 rgba(0, 0, 0, 0.25);
            border-radius: 10px;
            background-size: cover !important;
            background-position: top;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
        }

        .image-upload .thumb .profilePicPreview.logoPicPrev {
            background-size: contain !important;
            background-position: center;
        }

        .image-upload .thumb .profilePicUpload {
            font-size: 0;
            opacity: 0;
        }

        .image-upload .thumb .avatar-edit label {
            text-align: center;
            line-height: 45px;
            font-size: 18px;
            cursor: pointer;
            padding: 2px 25px;
            width: 100%;
            border-radius: 5px;
            box-shadow: 0 5px 10px 0 rgba(0, 0, 0, 0.16);
            transition: all 0.3s;
        }

        .image-upload .thumb .avatar-edit label:hover {
            transform: translateY(-3px);
        }

        .image-upload .thumb .profilePicPreview .remove-image {
            position: absolute;
            top: -9px;
            right: -9px;
            text-align: center;
            width: 55px;
            height: 55px;
            font-size: 24px;
            border-radius: 50%;
            background-color: #df1c1c;
            color: #fff;
            display: none;
        }

        .image-upload .thumb .profilePicPreview.has-image .remove-image {
            display: block;
        }

        .payment-method-item .payment-method-header .thumb .profilePicPreview {
            width: 210px;
            height: 210px;
            display: block;
            border: 3px solid #f1f1f1;
            box-shadow: 0 0 5px 0 rgba(0, 0, 0, 0.25);
            border-radius: 10px;
            background-size: cover;
            background-position: center
        }

        .payment-method-item .payment-method-header .thumb .profilePicUpload {
            font-size: 0;
            opacity: 0;
            width: 0;
        }

        .image-upload .thumb .profilePicUpload {
            font-size: 0;
            opacity: 0;
        }

        .bg--success {
            background-color: #28c76f !important;
            color: #ffffff;
        }
    </style>
@endpush
