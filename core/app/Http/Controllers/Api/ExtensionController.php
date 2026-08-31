<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AccountListing;
use App\Models\SocialMedia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExtensionController extends Controller
{
    public function __construct()
    {
        if (!headers_sent()) {
            @header("Access-Control-Allow-Origin: *");
            @header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
            @header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        }
    }

    /**
     * Helper to compute user validity text identical to web header
     */
    private function getUserValidity(User $user)
    {
        $expiryDate = $user->expires_at ?: ($user->created_at ? $user->created_at->addDays(30) : now()->addDays(30));
        $isExpired = $user->expires_at ? now()->greaterThanOrEqualTo($expiryDate) : false;
        if (!$user->expires_at && !$user->is_trial) {
            $isExpired = now()->greaterThanOrEqualTo($expiryDate);
        }

        $validityText = '';
        if ($user->is_trial) {
            if ($user->pending_trial_minutes > 0) {
                $validityText = 'Trial: Pending Start';
                $isExpired = false;
            } else {
                $diff = now()->diff(Carbon::parse($expiryDate));
                if ($isExpired) {
                    $validityText = 'Trial Expired';
                } elseif ($diff->days > 0) {
                    $validityText = 'Trial: ' . $diff->days . ' Days';
                } elseif ($diff->h > 0) {
                    $validityText = 'Trial: ' . $diff->h . ' Hours';
                } else {
                    $validityText = 'Trial: ' . $diff->i . ' Mins';
                }
            }
        } else {
            $daysRemaining = $isExpired ? 0 : (int) now()->startOfDay()->diffInDays(Carbon::parse($expiryDate)->startOfDay(), false);
            $validityText = $isExpired ? 'Expired' : ($daysRemaining . ' Days Remaining');
        }

        return [
            'is_expired'    => $isExpired,
            'validity_text' => $validityText,
            'expires_at'    => $expiryDate ? Carbon::parse($expiryDate)->toDateTimeString() : null,
        ];
    }

    /**
     * Mobile App Login endpoint
     */
    public function mobileLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        if (!auth()->attempt([$loginType => $request->username, 'password' => $request->password])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username/email or password',
            ], 401);
        }

        $user = auth()->user();

        if ($user->status == Status::USER_BAN) {
            auth()->logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account has been banned by administrator.',
            ], 403);
        }

        $validity = $this->getUserValidity($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user'    => [
                'id'            => $user->id,
                'name'          => $user->fullname,
                'username'      => $user->username,
                'email'         => $user->email,
                'is_expired'    => $validity['is_expired'],
                'validity_text' => $validity['validity_text'],
                'expires_at'    => $validity['expires_at'],
            ],
        ]);
    }

    /**
     * Get all platforms the user has access to via their plan
     */
    public function platforms(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'status'  => 401,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validity = $this->getUserValidity($user);


        if (!$user->plan_id && empty($user->account_ids)) {
            return response()->json([
                'success'       => true,
                'platforms'     => [],
                'validity_text' => $validity['validity_text'],
                'is_expired'    => $validity['is_expired'],
                'message'       => 'No plan or accounts assigned. Contact admin.',
            ]);
        }

        if ($validity['is_expired']) {
            return response()->json([
                'success'       => true,
                'platforms'     => [],
                'validity_text' => $validity['validity_text'],
                'is_expired'    => true,
                'message'       => 'Your subscription is expired. Please contact administrator.',
            ]);
        }

        $isAdmin = auth()->guard('admin')->check() || session()->get('is_admin_testing') === true || (bool) $user->is_tester;

        // All accounts the user can access via their plan OR specific assigned account_ids (same as web panel UserController)
        $assignedAccounts = collect();

        if ($isAdmin) {
            $adminAccounts = AccountListing::with('socialMedia')
                ->where('status', Status::LISTING_ACTIVE)
                ->whereHas('socialMedia', function($q) {
                    $q->active();
                })
                ->get();
            $assignedAccounts = $assignedAccounts->merge($adminAccounts);
        }

        if ($user->plan_id) {
            $planAccounts = AccountListing::with('socialMedia')
                ->where('plan_id', $user->plan_id)
                ->where('status', Status::LISTING_ACTIVE)
                ->whereHas('socialMedia', function($q) {
                    $q->active();
                })
                ->get();
            $assignedAccounts = $assignedAccounts->merge($planAccounts);
        }

        if (!empty($user->account_ids)) {
            $accountIds = (array) $user->account_ids;
            $specificAccounts = AccountListing::with('socialMedia')
                ->whereIn('id', $accountIds)
                ->where('status', Status::LISTING_ACTIVE)
                ->whereHas('socialMedia', function($q) {
                    $q->active();
                })
                ->get();
            $assignedAccounts = $assignedAccounts->merge($specificAccounts);
        }

        $assignedAccounts = $assignedAccounts->unique('id')->values();

        $platformCounters = [];
        $accountsList = $assignedAccounts;
        $countsByPlatform = $accountsList->groupBy('social_media_id')->map->count();

        $accounts = $accountsList
            ->map(function ($acc) use (&$platformCounters, $countsByPlatform) {
                $pId = $acc->socialMedia->id;
                $platformCounters[$pId] = ($platformCounters[$pId] ?? 0) + 1;
                $idx = $platformCounters[$pId];
                $totalCount = $countsByPlatform[$pId] ?? 1;

                $displayName = $totalCount > 1 
                    ? ($acc->socialMedia->name . ' ' . $idx) 
                    : $acc->socialMedia->name;

                return [
                    'id'           => $acc->socialMedia->id,
                    'account_id'   => $acc->id,
                    'name'         => $acc->socialMedia->name,
                    'title'        => $displayName,
                    'display_name' => $displayName,
                    'url'          => $acc->socialMedia->url,
                    'domain'       => $acc->socialMedia->domain,
                ];
            })
            ->values();

        return response()->json([
            'success'       => true,
            'platforms'     => $accounts,
            'validity_text' => $validity['validity_text'],
            'is_expired'    => $validity['is_expired'],
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }


    /**
     * Get cookies for a specific platform (by social_media_id)
     * The extension calls this when user clicks "Access" on a platform
     */
    public function getCookies(Request $request, $platformId, $accountId = null)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $isAdmin = auth()->guard('admin')->check() || session()->get('is_admin_testing') === true || (bool) ($user->is_tester ?? false);


        if (!$isAdmin && !$user->plan_id && empty($user->account_ids)) {
            return response()->json(['success' => false, 'message' => 'No active plan or accounts'], 403);
        }
        
        $validity = $this->getUserValidity($user);
        if (!$isAdmin && $validity['is_expired']) {
            return response()->json(['success' => false, 'message' => 'Your subscription is expired. Please contact administrator.'], 403);
        }

        $query = AccountListing::where('status', Status::LISTING_ACTIVE)->with('socialMedia');

        $targetAccountId = $accountId ?: $request->account_id;

        if ($targetAccountId) {
            $query->where('id', $targetAccountId);
        } else {
            $query->where('social_media_id', $platformId);
            if (!$isAdmin) {
                $allowedIds = (array) ($user->account_ids ?? []);
                $query->where(function($q) use ($user, $allowedIds) {
                    if ($user->plan_id) {
                        $q->where('plan_id', $user->plan_id);
                    }
                    if (!empty($allowedIds)) {
                        $q->orWhereIn('id', $allowedIds);
                    }
                });
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
        $validity = $this->getUserValidity($user);

        $planData = null;
        if (!$validity['is_expired']) {
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
                'id'            => $user->id,
                'name'          => $user->fullname,
                'username'      => $user->username,
                'email'         => $user->email,
                'validity_text' => $validity['validity_text'],
                'is_expired'    => $validity['is_expired'],
                'plan'          => $planData,
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
