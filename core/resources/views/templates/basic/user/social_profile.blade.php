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
                        @include($activeTemplate . 'partials.user_profile_topbar', ['profileMessage' => 'Manage your profile from the below'])
                        <div class="row">
                            <div class="col-lg-3 col-md-4">
                                @include($activeTemplate . 'partials.user_profile_sidenav')
                            </div>
                            <div class="col-lg-8 col-md-8">
                                <div class="profile-setting__body">
                                    <form method="post">
                                        @csrf
                                        <div class="row">
                                            <div class="col-sm-12 form-group">
                                                <div class="form--group">
                                                    <label class="form--label"> @lang('Facebook') </label>
                                                    <input class="form--control" name="facebook" type="url" value="{{ old('facebook',@$userSocialMedia->facebook) }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-12 form-group">
                                                <div class="form--group">
                                                    <label class="form--label"> @lang('Linkedin') </label>
                                                    <input class="form--control" name="linkedin" type="url" value="{{ old('linkedin',@$userSocialMedia->linkedin) }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-12 form-group">
                                                <div class="form--group">
                                                    <label class="form--label"> @lang('Instagram') </label>
                                                    <input class="form--control" name="instagram" type="url" value="{{ old('instagram',@$userSocialMedia->instagram) }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-12 form-group">
                                                <div class="form--group">
                                                    <label class="form--label" for="tter"> @lang('Twitter') </label>
                                                    <input class="form--control" name="twitter" type="url" value="{{ old('twitter',@$userSocialMedia->twitter) }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-12 form-group">
                                                <div class="form--group">
                                                    <label class="form--label" for="tter"> @lang('Youtube') </label>
                                                    <input class="form--control" name="youtube" type="url" value="{{ old('youtube',@$userSocialMedia->youtube) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-setting__button d-flex justify-content-end">
                                            <button class="btn btn--base " type="submit"> @lang('Save Changes') </button>
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
