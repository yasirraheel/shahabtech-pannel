<?php

namespace App\Http\Controllers\Admin;

use App\Lib\FormProcessor;
use App\Models\SocialMedia;
use App\Models\AccountListing;
use App\Models\User;
use App\Constants\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SocialMediaController extends Controller
{
    public function index()
    {
        $pageTitle    = 'Manage Platforms';
        $socialsMedia = SocialMedia::searchable(['name'])->withCount('accountListing')->orderBy('name')->paginate(getPaginate());
        return view('admin.social_media.index', compact('pageTitle', 'socialsMedia'));
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'name'         => 'required',
            'domain'       => 'required',
            'url'          => 'required|url',
            'instructions' => 'nullable|string',
        ]);

        if ($id) {
            $socialMedia   = SocialMedia::findOrFail($id);
            $notifyMessage = 'Platform updated successfully';
        } else {
            $socialMedia   = new SocialMedia();
            $notifyMessage = 'Platform added successfully';
        }

        $socialMedia->name         = $request->name;
        $socialMedia->domain       = $request->domain;
        $socialMedia->url          = $request->url;
        $socialMedia->instructions = $request->instructions;
        $socialMedia->save();

        $notify[] = ['success', $notifyMessage];
        return back()->withNotify($notify);
    }

    public function info($id)
    {
        $socialMedia = SocialMedia::findOrFail($id);
        $form        = $socialMedia->form;
        $pageTitle   = 'Add Social Media Information';
        return view('admin.social_media.add_info', compact('pageTitle', 'socialMedia', 'form'));
    }

    public function infoStore(Request $request, $id)
    {
        $formProcessor = new FormProcessor();

        $generatorValidation = $formProcessor->generatorValidation();
        $validation          =  $generatorValidation['rules'];
        $request->validate($validation, $generatorValidation['messages']);

        $socialMedia   = SocialMedia::findOrFail($id);
        $generate      = $formProcessor->generate('social_media', true, 'id', $socialMedia->form_id);

        $socialMedia->form_id = $generate->id;
        $socialMedia->save();

        $notify[] = ['success', 'Social media requirements updated successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return SocialMedia::changeStatus($id);
    }

    public function delete($id)
    {
        $socialMedia = SocialMedia::withCount('accountListing')->findOrFail($id);

        // Delete associated accounts
        foreach ($socialMedia->accountListing as $account) {
            $account->delete();
        }

        $socialMedia->delete();

        $notify[] = ['success', 'Platform and all associated accounts deleted successfully'];
        return back()->withNotify($notify);
    }

    public function loadBalance(Request $request, $id)
    {
        $mode = $request->input('mode', 'override_manual');
        if (!in_array($mode, ['override_manual', 'keep_manual'])) {
            $mode = 'override_manual';
        }

        $platform = SocialMedia::findOrFail($id);
        $updatedUsersCount = self::executeLoadBalance($platform->id, $mode);

        $modeText = $mode === 'override_manual' ? 'overriding manual assignments' : 'keeping manual assignments';
        $notify[] = ['success', "Load balancing completed for {$platform->name}: {$updatedUsersCount} active unexpired user(s) load balanced ({$modeText})."];
        return back()->withNotify($notify);
    }

    /**
     * Reusable Static Load Balancer using strict subscription & validity conditions.
     * Filter OUT expired/banned users and distribute active unexpired users EVENLY across active valid accounts.
     */
    public static function executeLoadBalance($platformId, $mode = 'override_manual')
    {
        $platform = SocialMedia::find($platformId);
        if (!$platform) return 0;

        // 1. Fetch all active & valid accounts for this platform (status = LISTING_ACTIVE and cookie_status != 0)
        $activeAccounts = AccountListing::where('social_media_id', $platform->id)
            ->where('status', Status::LISTING_ACTIVE)
            ->where('cookie_status', '!=', 0)
            ->get();

        $activeAccountIds = $activeAccounts->pluck('id')->toArray();
        $allPlatformAccountIds = AccountListing::where('social_media_id', $platform->id)->pluck('id')->toArray();
        $allPlatformAccountIdsInt = array_map('intval', $allPlatformAccountIds);
        $allPlatformAccountIdsStr = array_map('strval', $allPlatformAccountIds);

        // 2. STRIP all accounts belonging to this platform from EXPIRED, INACTIVE, and BANNED users
        $expiredUsers = User::where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '<=', now())
              ->orWhere('status', '!=', Status::USER_ACTIVE);
        })->where('is_tester', 0)->get();

        foreach ($expiredUsers as $expiredUser) {
            $currentAccountIds = (array) ($expiredUser->account_ids ?? []);
            $cleanedAccountIds = array_values(array_filter($currentAccountIds, function ($accId) use ($allPlatformAccountIdsInt, $allPlatformAccountIdsStr) {
                return !in_array((int)$accId, $allPlatformAccountIdsInt) && !in_array((string)$accId, $allPlatformAccountIdsStr);
            }));

            if (count($currentAccountIds) !== count($cleanedAccountIds)) {
                $expiredUser->account_ids = $cleanedAccountIds;
                $expiredUser->timestamps = false;
                $expiredUser->save();
                $expiredUser->timestamps = true;
            }
        }

        // 3. Fetch ONLY ACTIVE, NON-EXPIRED users (User::active())
        $validUsers = User::active()->where('is_tester', 0)->get();

        if ($validUsers->isEmpty()) {
            return 0;
        }

        if ($activeAccounts->isEmpty()) {
            // If NO valid active accounts exist, strip all assignments for this platform from valid users too
            foreach ($validUsers as $user) {
                $currentAccountIds = (array) ($user->account_ids ?? []);
                $cleanedAccountIds = array_values(array_filter($currentAccountIds, function ($accId) use ($allPlatformAccountIdsInt, $allPlatformAccountIdsStr) {
                    return !in_array((int)$accId, $allPlatformAccountIdsInt) && !in_array((string)$accId, $allPlatformAccountIdsStr);
                }));

                if (count($currentAccountIds) !== count($cleanedAccountIds)) {
                    $user->account_ids = $cleanedAccountIds;
                    $user->timestamps = false;
                    $user->save();
                    $user->timestamps = true;
                }
            }
            return 0;
        }

        $updatedUsersCount = 0;

        // Initialize account user counts map for active valid accounts
        $accountUserCounts = [];
        foreach ($activeAccountIds as $accId) {
            $accountUserCounts[(int) $accId] = 0;
        }

        if ($mode === 'keep_manual') {
            // Count valid users who will KEEP their existing valid assignment for this platform
            foreach ($validUsers as $user) {
                $currentAccountIds = array_map('intval', (array) ($user->account_ids ?? []));
                $userPlatformAccs = array_intersect($currentAccountIds, $allPlatformAccountIdsInt);
                
                if (!empty($userPlatformAccs)) {
                    $validAssignedAccs = array_intersect($userPlatformAccs, $activeAccountIds);
                    if (!empty($validAssignedAccs)) {
                        foreach ($validAssignedAccs as $existingAccId) {
                            if (isset($accountUserCounts[$existingAccId])) {
                                $accountUserCounts[$existingAccId]++;
                            }
                        }
                    }
                }
            }
        }

        foreach ($validUsers as $user) {
            $currentAccountIds = array_map('intval', (array) ($user->account_ids ?? []));
            $userPlatformAccs = array_intersect($currentAccountIds, $allPlatformAccountIdsInt);
            $validAssignedAccs = array_intersect($userPlatformAccs, $activeAccountIds);

            if ($mode === 'keep_manual' && !empty($validAssignedAccs)) {
                // User already has a VALID working account for this platform, KEEP IT!
                continue;
            }

            // Strip out ALL previous invalid/expired assignments for this platform
            $otherAccountIds = array_values(array_diff($currentAccountIds, $allPlatformAccountIdsInt));

            // Pick active valid account with the lowest current count (with random tie-breaker)
            $minCount = min($accountUserCounts);
            $lowestCandidateIds = array_keys($accountUserCounts, $minCount);
            $bestAccountId = $lowestCandidateIds[array_rand($lowestCandidateIds)];

            $otherAccountIds[] = (int) $bestAccountId;
            $accountUserCounts[$bestAccountId]++;

            $user->account_ids = array_values(array_unique($otherAccountIds));
            $user->timestamps = false;
            $user->save();
            $user->timestamps = true;

            $updatedUsersCount++;
        }

        return $updatedUsersCount;
    }
}
