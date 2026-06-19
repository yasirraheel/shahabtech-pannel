@extends('admin.layouts.app')
@section('panel')
    <div class="submitRequired bg--warning form-change-alert d-none"><i class="fas fa-exclamation-triangle"></i> @lang('You\'ve to click on the submit button to apply the changes')</div>
    <div class="row mb-none-30">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg--primary d-flex justify-content-between">
                    <h5 class="text-white">{{ __($socialMedia->name) }} @lang('Informations')</h5>
                    <button type="button" class="btn btn-sm btn-outline-light float-end form-generate-btn"> <i class="la la-fw la-plus"></i>@lang('Add New')</button>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.social.media.info.store', $socialMedia->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <x-generated-form :form=$form />
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <x-form-generator-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.social.media.index') }}" class="btn btn-sm btn-outline--dark">
        <i class="las la-undo"></i>@lang('Back')
    </a>

    
@endpush


{{-- @extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <form action="{{ route('admin.social.media.info.store', $socialMedia->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="payment-method-item">
                            <div class="payment-method-body">
                                <div class="card border--primary mt-3">
                                    <div class="card-header bg--primary d-flex justify-content-between">
                                        <h5 class="text-white"> {{ __($socialMedia->name) }} @lang('Informations')</h5>
                                        <button class="btn btn-sm btn-outline-light float-end form-generate-btn" type="button"> <i class="la la-fw la-plus"></i>@lang('Add New')</button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row addedField">
                                            @if ($form)
                                                @foreach ($form->form_data as $formData)
                                                    <div class="col-md-4">
                                                        <div class="card mb-3 border" id="{{ $loop->index }}">
                                                            <input name="form_generator[is_required][]" type="hidden" value="{{ $formData->is_required }}">
                                                            <input name="form_generator[extensions][]" type="hidden" value="{{ $formData->extensions }}">
                                                            <input name="form_generator[options][]" type="hidden" value="{{ implode(',', $formData->options) }}">

                                                            <div class="card-body">
                                                                <div class="form-group">
                                                                    <label>@lang('Label')</label>
                                                                    <input class="form-control" name="form_generator[form_label][]" type="text" value="{{ $formData->name }}" readonly>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>@lang('Type')</label>
                                                                    <input class="form-control" name="form_generator[form_type][]" type="text" value="{{ $formData->type }}" readonly>
                                                                </div>
                                                                @php
                                                                    $jsonData = json_encode([
                                                                        'type' => $formData->type,
                                                                        'is_required' => $formData->is_required,
                                                                        'label' => $formData->name,
                                                                        'extensions' => explode(',', $formData->extensions) ?? 'null',
                                                                        'options' => $formData->options,
                                                                        'old_id' => '',
                                                                    ]);
                                                                @endphp
                                                                <div class="btn-group w-100">
                                                                    <button class="btn btn--primary editFormData" data-form_item="{{ $jsonData }}" data-update_id="{{ $loop->index }}" type="button"><i class="las la-pen"></i></button>
                                                                    <button class="btn btn--danger removeFormData" type="button"><i class="las la-times"></i></button>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-form-generator />
@endsection

@push('script')
    <script>
        "use strict"
        var formGenerator = new FormGenerator();
        formGenerator.totalField = {{ $form ? count((array) $form->form_data) : 0 }}
    </script>

    <script src="{{ asset('assets/global/js/form_actions.js') }}"></script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.social.media.index') }}" />
@endpush --}}
