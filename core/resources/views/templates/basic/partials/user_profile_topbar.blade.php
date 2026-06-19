<div class="profile-setting__header">
    <div class="profile-setting__thumb">
        @if(auth()->user()->image)
        <img src="{{ getImage(getFilePath('userProfile') . '/' . old('image', auth()->user()->image), getFileSize('userProfile')) }}">
        @else
        <img src="{{ getImage($activeTemplateTrue . '/images/avatar.png') }}">
        @endif
    </div>
    <div class="profile-setting__content">
        <h4 class="profile-setting__header-title"> {{ auth()->user()->fullname }} / <span class="text"> {{ $pageTitle }} </span></h4>
        <p class="profile-setting__desc"> {{__($profileMessage)}} </p>
    </div>
</div>