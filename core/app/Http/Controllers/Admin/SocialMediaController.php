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
        
        // Get all active accounts for this platform
        $activeAccounts = AccountListing::where('social_media_id', $platform->id)
            ->where('status', Status::LISTING_ACTIVE)
            ->get();

        if ($activeAccounts->isEmpty()) {
            $notify[] = ['error', "Cannot load balance: No active accounts found for platform {$platform->name}."];
            return back()->withNotify($notify);
        }

        $activeAccountIds = $activeAccounts->pluck('id')->toArray();
        $allPlatformAccountIds = AccountListing::where('social_media_id', $platform->id)->pluck('id')->toArray();

        // Fetch all non-banned users
        $allUsers = User::where('status', '!=', Status::USER_BAN)->get();

        // Separate active unexpired users from expired users
        $now = now();
        $validUsers = collect();
        $expiredUsersCount = 0;

        foreach ($allUsers as $user) {
            $isExpired = !$user->expires_at || $user->expires_at <= $now;

            if ($isExpired) {
                // Strip all accounts belonging to this platform from expired user
                $currentAccountIds = array_map('intval', (array) ($user->account_ids ?? []));
                $otherAccountIds = array_values(array_diff($currentAccountIds, $allPlatformAccountIds));

                if (count($currentAccountIds) !== count($otherAccountIds)) {
                    $user->account_ids = $otherAccountIds;
                    $user->timestamps = false;
                    $user->save();
                    $user->timestamps = true;
                    $expiredUsersCount++;
                }
            } else {
                $validUsers->push($user);
            }
        }

        if ($validUsers->isEmpty()) {
            $notify[] = ['warning', "No active, non-expired subscribed users found to load balance. Removed assignments from {$expiredUsersCount} expired user(s)."];
            return back()->withNotify($notify);
        }

        $updatedUsersCount = 0;

        // Initialize account user counts map for active accounts
        $accountUserCounts = [];
        foreach ($activeAccountIds as $accId) {
            $accountUserCounts[(int) $accId] = 0;
        }

        if ($mode === 'keep_manual') {
            // Count valid users who will KEEP their existing manual assignment for this platform
            foreach ($validUsers as $user) {
                $currentAccountIds = array_map('intval', (array) ($user->account_ids ?? []));
                $userPlatformAccs = array_intersect($currentAccountIds, $allPlatformAccountIds);
                
                if (!empty($userPlatformAccs)) {
                    foreach ($userPlatformAccs as $existingAccId) {
                        if (isset($accountUserCounts[$existingAccId])) {
                            $accountUserCounts[$existingAccId]++;
                        }
                    }
                }
            }
        }

        foreach ($validUsers as $user) {
            $currentAccountIds = array_map('intval', (array) ($user->account_ids ?? []));
            $userPlatformAccs = array_intersect($currentAccountIds, $allPlatformAccountIds);
            
            if ($mode === 'keep_manual' && !empty($userPlatformAccs)) {
                // User already has an assignment for this platform, keep it!
                continue;
            }

            // Strip out ALL previous assignments for this platform
            $otherAccountIds = array_diff($currentAccountIds, $allPlatformAccountIds);

            // Pick the active account with the lowest current count
            asort($accountUserCounts);
            $bestAccountId = key($accountUserCounts);

            $otherAccountIds[] = $bestAccountId;
            $accountUserCounts[$bestAccountId]++;

            $user->account_ids = array_values(array_unique($otherAccountIds));
            $user->timestamps = false;
            $user->save();
            $user->timestamps = true;

            $updatedUsersCount++;
        }

        $modeText = $mode === 'override_manual' ? 'overriding manual assignments' : 'keeping manual assignments';
        $notify[] = ['success', "Load balancing completed for {$platform->name}: {$updatedUsersCount} active unexpired user(s) load balanced across {$activeAccounts->count()} active account(s) ({$modeText}). Cleared accounts from {$expiredUsersCount} expired user(s)."];
        return back()->withNotify($notify);
    }
}
