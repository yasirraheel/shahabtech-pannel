<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\UserSocialMedia;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\UserNotificationPermission;

class ProfileController extends Controller
{
    public function profile()
    {
        $pageTitle = "Profile Setting";
        $user = auth()->user();
        return view('Template::user.profile_setting', compact('pageTitle','user'));
    }

    public function generalProfile()
    {
        $pageTitle = 'General Profile';
        return view('Template::user.general_profile', compact('pageTitle'));
    }

    public function submitProfile(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ],[
            'firstname.required'=>'The first name field is required',
            'lastname.required'=>'The last name field is required'
        ]);

        $user = auth()->user();

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;

        $user->address = $request->address;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->zip = $request->zip;
        $user->description = $request->description;

        if ($request->hasFile('image')) {
            try {
                $old = $user->image;
                $user->image = fileUploader($request->image, getFilePath('userProfile'), getFileSize('userProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your profile photo'];
                return back()->withNotify($notify)->withInput();
            }
        }

        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return back()->withNotify($notify);
    }

    public function changePassword()
    {
        $pageTitle = 'Change Password';
        return view('Template::user.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request)
    {

        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $request->validate([
            'current_password' => 'required',
            'password' => ['required','confirmed',$passwordValidation]
        ]);

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = ['success', 'Password changed successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'The password doesn\'t match!'];
            return back()->withNotify($notify);
        }
    }

    public function socialProfile()
    {
        $pageTitle = 'Social Profile';
        $userSocialMedia = UserSocialMedia::where('user_id', auth()->user()->id)->first();
        return view('Template::user.social_profile', compact('pageTitle', 'userSocialMedia'));
    }

    public function socialProfilePost(Request $request)
    {
        $request->validate([
            'facebook'  => 'nullable|url',
            'linkedin'  => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter'   => 'nullable|url',
            'youtube'   => 'nullable|url'
        ]);

        $userSocialMedia = UserSocialMedia::where('user_id', auth()->user()->id)->first();

        if (!$userSocialMedia) {
            $userSocialMedia       = new UserSocialMedia();
            $userSocialMedia->user_id = auth()->id();
        }


        $userSocialMedia->facebook  = $request->facebook;
        $userSocialMedia->linkedin  = $request->linkedin;
        $userSocialMedia->instagram = $request->instagram;
        $userSocialMedia->twitter   = $request->twitter;
        $userSocialMedia->youtube   = $request->youtube;
        $userSocialMedia->save();

        $notify[] = ['success', 'Social profile update successfully'];
        return back()->withNotify($notify);
    }

    public function notificationPermission()
    {
        $pageTitle = 'Notification Permission';
        $notification = UserNotificationPermission::where('user_id', auth()->user()->id)->first();
        return view('Template::user.notification_permission', compact('pageTitle', 'notification'));
    }

    public function notificationPermissionPost(Request $request)
    {
        $request->validate([
            'approved'   => 'nullable|in:1',
            'reject'     => 'nullable|in:1',
            'bid'        => 'nullable|in:1',
            'buy'        => 'nullable|in:1',
            'refund'     => 'nullable|in:1',
            'sell'       => 'nullable|in:1',
            'cancel_bid' => 'nullable|in:1',
        ]);

        $notification = UserNotificationPermission::where('user_id', auth()->user()->id)->first();
        $notification->approved = $request->approved ?? 0;
        $notification->reject = $request->reject ?? 0;
        $notification->bid = $request->bid ?? 0;
        $notification->buy = $request->buy ?? 0;
        $notification->refund = $request->refund ?? 0;
        $notification->sell = $request->sell ?? 0;
        $notification->cancel_bid = $request->cancel_bid ?? 0;
        $notification->save();

        $notify[] = ['success', 'Notification permission update successfully'];
        return back()->withNotify($notify);
    }

}
