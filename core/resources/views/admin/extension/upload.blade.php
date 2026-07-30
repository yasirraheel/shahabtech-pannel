@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">
        <div class="col-xl-12 col-md-12 mb-30">
            <div class="card b-radius--10 ">
                <div class="card-body">
                    <h5 class="card-title mb-4">@lang('Upload Extension (.zip)')</h5>

                    <form action="{{ route('admin.extension.upload.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Extension File (.zip)')</label>
                                    <div class="custom-file">
                                        <input type="file" class="form-control" name="extension_zip" id="customFile" accept=".zip">
                                    </div>
                                    <small class="text-muted">@lang('Leave empty if updating settings only')</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Minimum Required Extension Version')</label>
                                    <input type="text" class="form-control" name="min_extension_version" value="{{ $minVersion }}" placeholder="e.g. 1.9.6" required>
                                    <small class="text-muted">@lang('Version threshold for update notification')</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Strict Force Update Mode')</label>
                                    <div class="form-check form-switch mt-2">
                                        <input type="checkbox" class="form-check-input" name="force_extension_update" value="1" id="forceUpdateSwitch" {{ $forceUpdate ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2" for="forceUpdateSwitch">
                                            <strong>{{ $forceUpdate ? __('STRICT FORCE UPDATE') : __('SOFT REMINDER (6-Hour Snooze Allowed)') }}</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">@lang('Strict Mode blocks usage. Soft Mode shows dialog every 6h with Cancel/Skip.')</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save & Upload Extension')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card b-radius--10 mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">@lang('Distribution Link')</h5>
                    
                    @if($extensionExists)
                        <div class="alert alert-success">
                            <h4 class="alert-heading">@lang('Extension is currently available for download!')</h4>
                            <p>@lang('Last uploaded on'): <strong>{{ $lastModified }}</strong></p>
                            <hr>
                            <p class="mb-0">@lang('Share the link below with your users. Clicking it will automatically download the extension.')</p>
                        </div>

                        <div class="form-group">
                            <label>@lang('Direct Download Link')</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $downloadUrl }}" readonly id="downloadLink">
                                <button class="btn btn--primary copy-btn" type="button" data-clipboard-target="#downloadLink"><i class="las la-copy"></i> @lang('Copy')</button>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h4 class="alert-heading">@lang('No extension uploaded yet.')</h4>
                            <p>@lang('Please upload a .zip file above to generate the distribution link.')</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    (function($){
        "use strict";
        $('.copy-btn').on('click', function () {
            var copyText = document.getElementById("downloadLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            notify('success', 'Copied: ' + copyText.value);
        });
    })(jQuery);
</script>
@endpush
