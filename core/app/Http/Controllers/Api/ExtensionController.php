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

        $expiryDate = $user->expires_at ?: $user->created_at->addDays(30);
        $isExpired = now()->greaterThanOrEqualTo($expiryDate);
        if ($isExpired) {
            return response()->json([
                'success'   => true,
                'platforms' => [],
                'message'   => 'Your subscription is expired. Please contact administrator.',
            ]);
        }

        // Get all active accounts for user's plan OR specific account
        $query = AccountListing::where('status', Status::LISTING_ACTIVE)
            ->where('cookie_status', '!=', 0)
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
            ->map(function ($acc) {
                return [
                    'id'           => $acc->socialMedia->id,
                    'account_id'   => $acc->id,
                    'name'         => $acc->socialMedia->name,
                    'title'        => $acc->title,
                    'display_name' => $acc->socialMedia->name . ($acc->title ? ' (' . $acc->title . ')' : ''),
                    'url'          => $acc->socialMedia->url,
                    'domain'       => $acc->socialMedia->domain,
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
    public function getCookies(Request $request, $platformId, $accountId = null)
    {
        $user = $request->user();
        $isAdmin = auth()->guard('admin')->check() || session()->get('is_admin_testing') === true || (bool) $user->is_tester;

        if (!$isAdmin && !$user->plan_id && empty($user->account_ids)) {
            return response()->json(['success' => false, 'message' => 'No active plan or accounts'], 403);
        }
        
        $expiryDate = $user->expires_at ?: $user->created_at->addDays(30);
        $isExpired = now()->greaterThanOrEqualTo($expiryDate);
        if (!$isAdmin && $isExpired) {
            return response()->json(['success' => false, 'message' => 'Your subscription is expired. Please contact administrator.'], 403);
        }

        $query = AccountListing::where('status', Status::LISTING_ACTIVE)->with('socialMedia');

        $targetAccountId = $accountId ?: $request->account_id;

        if ($targetAccountId) {
            $query->where('id', $targetAccountId);
        } else {
            $query->where('social_media_id', $platformId);
            if (!$isAdmin) {
                if ($user->plan_id) {
                    $query->where('plan_id', $user->plan_id);
                } else {
                    $query->whereIn('id', $user->account_ids ?? []);
                }
            }
        }

        $account = $query->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'No account available for this platform'], 404);
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
        $expiryDate = $user->expires_at ?: $user->created_at->addDays(30);
        $isExpired = now()->greaterThanOrEqualTo($expiryDate);

        $planData = null;
        if (!$isExpired) {
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
        }

        return response()->json([
            'success'          => true,
            'required_version' => gs('min_extension_version') ?: '1.9.6',
            'force_update'     => (bool) gs('force_extension_update'),
            'download_url'     => getExtensionDownloadUrl(),
            'user'             => [
                'id'       => $user->id,
                'name'     => $user->fullname,
                'username' => $user->username,
                'email'    => $user->email,
                'plan'     => $planData,
            ],
        ]);
    }

    /**
     * Public extension version check endpoint
     */
    public function version()
    {
        return response()->json([
            'success'          => true,
            'required_version' => gs('min_extension_version') ?: '1.9.6',
            'force_update'     => (bool) gs('force_extension_update'),
            'download_url'     => getExtensionDownloadUrl(),
        ]);
    }
}
