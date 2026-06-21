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
     * Get all platforms the user has access to via their plan
     */
    public function platforms(Request $request)
    {
        $user = $request->user();

        if (!$user->plan_id && empty($user->account_ids)) {
            return response()->json([
                'success'   => true,
                'platforms' => [],
                'message'   => 'No plan or accounts assigned. Contact admin.',
            ]);
        }

        // Get all active accounts for user's plan OR specific account
        $query = AccountListing::where('status', Status::LISTING_ACTIVE)
            ->whereHas('socialMedia', function($q) {
                $q->active();
            })
            ->with('socialMedia');

        if ($user->plan_id) {
            $query->where('plan_id', $user->plan_id);
        } else {
            $query->whereIn('id', $user->account_ids ?? []);
        }

        $accounts = $query->get()
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

        if (!$user->plan_id && empty($user->account_ids)) {
            return response()->json(['success' => false, 'message' => 'No active plan or accounts'], 403);
        }

        $query = AccountListing::where('social_media_id', $platformId)
            ->where('status', Status::LISTING_ACTIVE)
            ->with('socialMedia');

        if ($user->plan_id) {
            $query->where('plan_id', $user->plan_id);
        } else {
            $query->whereIn('id', $user->account_ids ?? []);
        }

        $account = $query->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'No account available for this platform on your plan/assignment'], 404);
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
        
        $planData = null;
        if ($user->plan) {
            $planData = [
                'id'   => $user->plan->id,
                'name' => $user->plan->name,
            ];
        } elseif (!empty($user->account_ids)) {
            $planData = [
                'id'   => 0,
                'name' => 'Direct Access',
            ];
        }

        return response()->json([
            'success' => true,
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->fullname,
                'username' => $user->username,
                'email'    => $user->email,
                'plan'     => $planData,
            ],
        ]);
    }
}
