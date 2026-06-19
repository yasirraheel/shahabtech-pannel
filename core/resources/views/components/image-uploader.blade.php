@props([
    'type' => null,
    'image' => null,
    'imagePath' => null,
    'size' => null,
    'name' => 'image',
    'id' => 'image-upload-input1',
    'accept' => '.png, .jpg, .jpeg',
    'required' => true,
    'darkMode' => false,
])
@php
    $size = $size ?? getFileSize($type);
    $imagePath = $imagePath ?? getImage(getFilePath($type) . '/' . $image, $size);
@endphp
<div {{ $attributes->merge(['class' => 'image--uploader']) }}>
    <div class="image-upload-wrapper {{ @$image ? 'hasDark' : '' }}">
        <div class="image-upload-preview {{ $darkMode ? 'bg--dark' : '' }}" style="background-image: url('{{ $imagePath }}')">
        </div>
        <div class="image-upload-input-wrapper">
            <input class="image-upload-input" id="{{ $id }}" name="{{ $name }}" type="file" accept="{{ $accept }}" @required($required)>
            <label class="bg--primary bg--base" for="{{ $id }}"><i class="la la-cloud-upload"></i></label>
        </div>
    </div>

    <div class="mt-2">
        <small class="text-muted mt-3"> @lang('Supported Files:')
            <b>{{ $accept }}.</b>
            @if ($size)
                @lang('Image will be resized into') <b>{{ $size }}</b>@lang('px')
            @endif
        </small>
    </div>
</div>
