@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="profile-setting-section py-120">
        <div class="container">
            <div class="row justify-content-center gy-4">
                <div class="col-12">
                    <div class="profile-filter d-md-none d-block text-end">
                        <button class="profile-filter__button toggle-profile-sidebar" type="button">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>
                </div>
                <div class="col-xl-10">
                    <div class="profile-setting">
                        @include($activeTemplate . 'partials.user_profile_topbar', [
                            'profileMessage' => 'Edit your profile information below',
                        ])
                        <div class="row">
                            <div class="col-lg-3 col-md-4">
                                @include($activeTemplate . 'partials.user_profile_sidenav')
                            </div>
                            <div class="col-lg-8 col-md-8">
                                <div class="profile-setting__body">
                                    <form method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="profile-setting__body-header profileParentImage">
                                            <div class="profile-setting__thumb profile-setting__thumb__custom">
                                                @if ($user->image)
                                                    <img class="showProfilePhoto" src="{{ getImage(getFilePath('userProfile') . '/' . old('image', $user->image), getFileSize('userProfile')) }}">
                                                @else
                                                    <img class="showProfilePhoto" src="{{ getImage($activeTemplateTrue . '/images/avatar.png') }}">
                                                @endif
                                                <label class="profile-image-label" for="profile-image"><i
                                                       class="las la-pencil-alt"></i></label>
                                                <input class="form--control form-two profilePicUpload" id="profile-image" id="formFile" name="image" type="file">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 form-group">
                                                <label class="form--label">@lang('First Name')</label>
                                                <input class="form--control" name="firstname" type="text" value="{{ $user->firstname }}" required>
                                            </div>
                                            <div class="col-sm-6 form-group">
                                                <label class="form--label">@lang('Last Name')</label>
                                                <input class="form--control" name="lastname" type="text" value="{{ $user->lastname }}" required>
                                            </div>
                                            <div class="col-sm-6 form-group">
                                                <label class="form-label">@lang('Address')</label>
                                                <input class="form--control" name="address" type="text" value="{{ @$user->address }}">
                                            </div>
                                            <div class="col-sm-6 form-group">
                                                <label class="form-label">@lang('State')</label>
                                                <input class="form--control" name="state" type="text" value="{{ @$user->state }}">
                                            </div>
                                            <div class="col-sm-6 form-group">
                                                <label class="form-label">@lang('Zip Code')</label>
                                                <input class="form--control" name="zip" type="text" value="{{ @$user->zip }}">
                                            </div>
                                            <div class="col-sm-6 form-group">
                                                <label class="form-label">@lang('City')</label>
                                                <input class="form--control" name="city" type="text" value="{{ @$user->city }}">
                                            </div>
                                            <div class="col-sm-12 form-group">
                                                <label class="form--label"> @lang('Description') </label>
                                                <textarea class="form--control" name="description">{{ @$user->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="profile-setting__button d-flex justify-content-end">
                                            <button class="btn btn--base" data-bs-toggle="modal" data-bs-target="#exampleModal2" type="submit"> @lang('Save Changes') </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict'

        $(".profilePicUpload").on('change', function() {
            proPicURL(this);
        });

        function proPicURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.showProfilePhoto').prop('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(".remove-image").on('click', function() {
            $(this).parents(".profileParentImage").find('input[type=file]').val('');
            $(this).parents(".profileParentImage").find('.showProfilePhoto').prop("src",
                "{{ getImage($activeTemplateTrue . '/images/avatar.png') }}");
        });
    </script>
@endpush
