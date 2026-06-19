<div class="profile-setting__sidebar">
    <span class="close-sidebar d-lg-none d-block close-profile-sidebar">
        <i class="las la-times"></i>
    </span>
    <ul class="setting-list">
        <li class="setting-list__item {{menuActive('user.general.profile')}}"><a class="setting-list__link" href="{{route('user.general.profile')}}"> @lang('General') </a></li>
        <li class="setting-list__item {{menuActive('user.profile.setting')}} "><a class="setting-list__link" href="{{route('user.profile.setting')}}"> @lang('Edit Profile') </a></li>
        <li class="setting-list__item {{menuActive('user.change.password')}}"><a class="setting-list__link" href="{{route('user.change.password')}}"> @lang('Change Password') </a></li>
        <li class="setting-list__item {{menuActive('user.social.profile')}}"><a class="setting-list__link" href="{{route('user.social.profile')}}"> @lang('Social Profile') </a></li>
        <li class="setting-list__item {{menuActive('user.notification.permission')}}"><a class="setting-list__link" href="{{route('user.notification.permission')}}"> @lang('Notifications') </a></li>
    </ul>
</div>