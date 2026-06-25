@extends('admin.layouts.app')
@section('panel')

    <div class="row mb-none-30">
        <div class="col-lg-3 col-md-3 mb-30">

            <div class="card b-radius--5 overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-flex p-3 bg--primary align-items-center">
                        <div class="avatar avatar--lg">
                            <img src="{{ getImage(getFilePath('adminProfile').'/'. $admin->image,getFileSize('adminProfile'))}}" alt="Image">
                        </div>
                        <div class="ps-3">
                            <h4 class="text--white">{{__($admin->name)}}</h4>
                        </div>
                    </div>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Name')
                            <span class="fw-bold">{{ __($admin->name) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            <span  class="fw-bold">{{ __($admin->username) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Email')
                            <span  class="fw-bold">{{ $admin->email }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-9 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4 border-bottom pb-2">@lang('Change Password')</h5>

                    <form action="{{ route('admin.password.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label>@lang('New Password')</label>
                            <div class="input-group">
                                <input class="form-control" type="password" name="password" id="password" required>
                                <span class="input-group-text toggle-password" data-target="password" style="cursor: pointer;">
                                    <i class="las la-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>@lang('Confirm Password')</label>
                            <div class="input-group">
                                <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required>
                                <span class="input-group-text toggle-password" data-target="password_confirmation" style="cursor: pointer;">
                                    <i class="las la-eye"></i>
                                </span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary w-100 btn-lg h-45">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('breadcrumb-plugins')
    <a href="{{route('admin.profile')}}" class="btn btn-sm btn-outline--primary" ><i class="las la-user"></i>@lang('Profile Setting')</a>
@endpush
@push('style')
    <style>
        .list-group-item:first-child{
            border-top-left-radius:unset;
            border-top-right-radius:unset;
        }
    </style>
@endpush
@push('script')
<script>
    (function($){
        "use strict";
        $('.toggle-password').on('click', function() {
            let target = $('#' + $(this).data('target'));
            let icon = $(this).find('i');
            if (target.attr('type') === 'password') {
                target.attr('type', 'text');
                icon.removeClass('la-eye').addClass('la-eye-slash');
            } else {
                target.attr('type', 'password');
                icon.removeClass('la-eye-slash').addClass('la-eye');
            }
        });
    })(jQuery);
</script>
@endpush
