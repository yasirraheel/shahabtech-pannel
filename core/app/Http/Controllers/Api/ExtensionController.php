<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Plan;
use App\Models\SocialMedia;
use App\Models\AccountListing;
use App\Constants\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExtensionController extends Controller
{
    /**
     * Login: returns user token + plan info
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where(function ($q) use ($request) {
            $q->where('username', $request->username)
              ->orWhere('email', $request->username);
        })->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        if ($user->status != Status::USER_ACTIVE) {
            return response()->json(['success' => false, 'message' => 'Account is banned or inactive'], 403);
        }

        $token = $user->createToken('extension')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->fullname,
                'username' => $user->username,
                'email'    => $user->email,
                'plan'     => $user->plan ? [
                    'id'   => $user->plan->id,
                    'name' => $user->plan->name,
                ] : null,
            ],
        ]);
    }

    /**
     * Get all platforms the user has access to via their plan
     */
    public function platforms(Request $request)
    {
        $user = $request->user();

        if (!$user->plan_id) {
            return response()->json([
                'success'   => true,
                'platforms' => [],
                'message'   => 'No plan assigned. Contact admin.',
            ]);
        }

        // Get all active accounts for user's plan
        $accounts = AccountListing::where('plan_id', $user->plan_id)
            ->where('status', Status::LISTING_ACTIVE)
            ->with('socialMedia')
            ->get()
            ->groupBy('social_media_id')
            ->map(function ($group) {
                $sm = $group->first()->socialMedia;
                return [
                    'id'     => $sm->id,
                    'name'   => $sm->name,
                    'url'    => $sm->url,
                    'domain' => $sm->domain,
                ];
            })
            ->values();

        return response()->json([
            'success'   => true,
            'platforms' => $accounts,
        ]);
    }

    /**
     * Get cookies for a specific platform (by social_media_id)
     * The extension calls this when user clicks "Access" on a platform
     */
    public function getCookies(Request $request, $platformId)
    {
        $user = $request->user();

        if (!$user->plan_id) {
            return response()->json(['success' => false, 'message' => 'No active plan'], 403);
        }

        $account = AccountListing::where('social_media_id', $platformId)
            ->where('plan_id', $user->plan_id)
            ->where('status', Status::LISTING_ACTIVE)
            ->with('socialMedia')
            ->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'No account available for this platform on your plan'], 404);
        }

        $cookies = $account->account_info;
        if (is_string($cookies)) {
            $cookies = json_decode($cookies, true);
        }

        return response()->json([
            'success'  => true,
            'platform' => [
                'name'   => $account->socialMedia->name,
                'url'    => $account->socialMedia->url,
                'domain' => $account->socialMedia->domain,
            ],
            'cookies'  => $cookies ?? [],
        ]);
    }

    /**
     * Get logged in user info + plan
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('plan');
        return response()->json([
            'success' => true,
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->fullname,
                'username' => $user->username,
                'email'    => $user->email,
                'plan'     => $user->plan ? [
                    'id'   => $user->plan->id,
                    'name' => $user->plan->name,
                ] : null,
            ],
        ]);
    }

    /**
     * Logout / revoke token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out']);
    }
}
